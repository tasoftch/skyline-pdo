<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\PDO\Compiler;


use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerContext;
use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\PDO\Compiler\Structure\SQL\MySQL;
use Skyline\PDO\Compiler\Structure\SQL\SerializerInterface;
use Skyline\PDO\Compiler\Structure\SQL\SQLite;
use Skyline\PDO\Compiler\Structure\Table\FieldInterface;
use Skyline\PDO\Compiler\Structure\Table\Table;
use Skyline\PDO\Compiler\Structure\Table\TableInterface;
use Skyline\PDO\Config\PDOFactory;

class PDOCompiler extends AbstractCompiler
{
    public $sqlMap = [
        "mysql" => MySQL::class,
        "sqlite" => SQLite::class
    ];

    /**
     * Create a mapped serializer to generate SQL for the given driver
     *
     *
     * @param \PDO|NULL $PDO
     * @return SerializerInterface
     * @throws \Exception
     */
    public function createSerializer(\PDO $PDO = NULL): SerializerInterface {
        static $ser = NULL;
        if(!$ser) {
            $cn = $this->sqlMap[ $PDO->getAttribute( \PDO::ATTR_DRIVER_NAME ) ] ?? NULL;
            if(!$cn)
                throw new \Exception("Can not create SQL for used driver");

            /** @var SerializerInterface $sqlGen */
            $ser = new $cn($PDO);
        }
        return $ser;
    }


    public function compile(CompilerContext $context)
    {
        if($context->includePDOResolving()) {
            if($sm = $context->getServiceManager()) {
                /** @var \PDO $PDO */
                $PDO = $sm->get( PDOFactory::PDO_SERVICE );

                $collectedTables = [];

                foreach($context->getSourceCodeManager()->yieldSourceFiles("/\.pdo\.php$/i") as $source) {
                    $tables = silent_include($source);
                    if(is_iterable($tables)) {
                        foreach($tables as $table) {
                            if($table instanceof TableInterface) {
                                $collectedTables[ $table->getName() ][] = $table;
                            }
                        }
                    }
                }


                foreach($collectedTables as $name => &$tables) {
                    $theTable = new Table($name);
                    $contents = [];

                    /** @var TableInterface $table */
                    foreach($tables as $table) {
                        foreach($table->getFieldObjects() as $nam => $field) {
                            $theTable->addField($field);
                        }
                        if($table->getContents())
                            $contents = array_merge($contents, $table->getContents());
                    }

                    $theTable->setContents($contents);
                    $tables = $theTable;
                }

                $serializer = $this->createSerializer( $PDO );


                foreach($collectedTables as $table) {
                    $tableName = $table->getName();
                    try {
                        $result = @$PDO->prepare("SELECT 1 FROM $tableName LIMIT 1");
                        $result = $result->execute();

                        echo "TABLE $tableName EXISTS.\n";

                        foreach($table->getFieldObjects() as $object) {
                            try {
                                $name = $object->getName();
                                $res = @$PDO->prepare("SELECT `$name` FROM $tableName LIMIT 1")->execute();
                            } catch (\PDOException $exception) {
                                $res = false;
                            }

                            if(!$res) {
                                echo "**    FIELD $tableName.$name DOES NOT EXIST.\nTRIES TO CREATE... ";

                                $sql =  "ALTER TABLE $tableName ADD " . $serializer->serializeField($object);
                                try {
                                    $PDO->exec($sql);
                                    echo "Success.\n";
                                } catch (\PDOException $exception) {
                                    echo "Failed: ", $exception->getMessage(), "\n";
                                }
                            } else
                                echo "**    FIELD $tableName.$name EXISTS.\n";
                        }
                    } catch (\PDOException $exception) {
                        $result = false;
                    }

                    if(!$result) {
                        echo "** TABLE $tableName DOES NOT EXIST.\n";
                        if($table->isOptional() == false) {
                            echo "** TRIES TO CREATE ... ";
                            try {
                                $PDO->exec( $serializer->serializeTable( $table ) );
                                echo "Success.\n";
                            } catch (\PDOException $exception) {
                                echo "Failed: ", $exception->getMessage(), "\n";
                            }
                        }
                    }


                    if($contents = $table->getContents()) {
                        foreach($contents as $content) {
                            try {
                                $PDO->exec("INSERT INTO $tableName " . $serializer->serializeContentRow($content));
                                echo "** CONTENT INSERTED.\n";
                            } catch (\PDOException $exception) {
                                echo "** INSERT FAILED: ", $exception->getMessage(), "\n";
                                print_r($content);
                            }

                        }
                    }


                    error_clear_last();
                }
            }
        } else {
            echo "** PDO Resolving skipped. Use option --with-pdo for compilation.\n";
        }
    }

    public function getCompilerName(): string
    {
        return "PDO Compiler";
    }
}

function silent_include($source) {
    return require $source;
}