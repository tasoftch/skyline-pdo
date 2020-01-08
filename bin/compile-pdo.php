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

use Skyline\PDO\Compiler\Structure\SQL\MySQL;
use Skyline\PDO\Compiler\Structure\SQL\SQLite;
use Skyline\PDO\Compiler\Structure\Table\Table;
use Skyline\PDO\Compiler\Structure\Table\TableInterface;

$pdo_files = glob("*.pdo.php");


if($pdo_files) {
    $options = getopt("su:p:d:cv:", ["pdo:"]);

    require $options["v"] ?? "vendor/autoload.php";

    $collectedTables = [];

    foreach ($pdo_files as $file) {
        $content = @include $file;
        if(is_iterable($content)) {
            foreach($content as $item) {
                if($item instanceof TableInterface) {
                    $collectedTables[ $item->getName() ][] = $item;
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

    if($collectedTables) {


        if(isset($options["s"])) {
            // SQL only
            $driver = $options["d"] ?? "sqlite";
            switch (strtolower( $driver )) {
                case 'mysql':
                    $serializer = new MySQL();
                    break;
                case 'sqlite':
                    $serializer = new SQLite();
                    break;
                default:
                    trigger_error("Can not create SQL for driver $driver", E_USER_ERROR);
            }


            echo "/** SQL <$driver> \n * CREATED BY TASOFT APPLICATIONS, SKYLINE CMS PDO COMPILER\n */\n";
            foreach($collectedTables as $table) {
                echo $serializer->serializeTable($table), "\n";
                if(isset($options["c"])) {
                    foreach($table->getContents() as $record) {
                        echo "INSERT INTO ", $table->getName(), " ", $serializer->serializeContentRow($record), ";\n";
                    }
                }
            }
            return;
        }


        if(!isset($options["pdo"])) {
            printf("Usage: compile-pdo.php [options] --pdo <pdo-dsn> [-u <username>] [-p <password>]\n");
            die();
        }

        $PDO = new \TASoft\Util\PDO($options["pdo"], $options["u"] ?? NULL, $options["p"] ?? NULL);

        $driver = $options["d"] ?? "sqlite";
        switch (strtolower( $PDO->getAttribute( PDO::ATTR_DRIVER_NAME ) )) {
            case 'mysql':
                $serializer = new MySQL();
                break;
            case 'sqlite':
                $serializer = new SQLite();
                break;
            default:
                trigger_error("Can not create SQL for driver $driver", E_USER_ERROR);
        }

        $checkTableName = function($tableName) use (&$PDO) {
            if($PDO instanceof PDO) {
                try {
                    $result = @$PDO->prepare("SELECT 1 FROM $tableName LIMIT 1")->execute();
                    return $result;
                } catch (Exception $exception) {
                }
            }
            return false;
        };

        $checkFieldName = function($fieldName, $tableName) use (&$PDO) {
            if($PDO instanceof PDO) {
                try {
                    $result = @$PDO->prepare("SELECT `$fieldName` FROM $tableName LIMIT 1")->execute();
                    return $result;
                } catch (Exception $exception) {
                }
            }
            return false;
        };


        foreach($collectedTables as $table) {
            $tableName = $table->getName();

            if($checkTableName($table->getName())) {
                echo "TABLE $tableName EXISTS.\n";

                foreach($table->getFieldObjects() as $object) {
                    $fieldName = $object->getName();

                    if($checkFieldName($fieldName, $tableName)) {
                        echo "**    FIELD $tableName.$fieldName EXISTS.\n";
                    } else {
                        echo "**    FIELD $tableName.$fieldName DOES NOT EXIST.\nTRIES TO CREATE... ";

                        $sql =  "ALTER TABLE $tableName ADD " . $serializer->serializeField($object);
                        try {
                            $PDO->exec($sql);
                            echo "Success.\n";
                        } catch (\PDOException $exception) {
                            echo "Failed: ", $exception->getMessage(), "\n";
                        }
                    }
                }
            } else {
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

            try {
                if(isset($options["c"])) {
                    foreach($table->getContents() as $record) {
                        $PDO->exec( "INSERT INTO " . $table->getName() . " " . $serializer->serializeContentRow($record) );
                    }
                }
            } catch (Exception $exception) {
                echo "INSERT: ", $exception->getMessage(), "\n";
            }
        }
    }
} else
    echo "N: No pdo definition files found (looking for *.pdo.php files)\n";
