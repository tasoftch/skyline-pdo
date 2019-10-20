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

namespace Skyline\PDO\Compiler\Structure\Table;


class Table implements TableInterface
{
    /** @var string */
    private $name;
    private $optional = false;
    private $fieldObjects = [];
    private $contents;

    /**
     * Table constructor.
     * @param string $name
     */
    public function __construct(string $name, bool $optional = false)
    {
        $this->name = $name;
        $this->optional = $optional;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return FieldInterface[]
     */
    public function getFieldObjects(): array
    {
        return $this->fieldObjects;
    }

    /**
     * @return mixed
     */
    public function getContents(): ?array
    {
        return $this->contents;
    }

    /**
     * @param array $contents
     * @return static
     */
    public function setContents(array $contents) {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @param FieldInterface $field
     * @return static
     */
    public function addField(FieldInterface $field) {
        if(isset($this->fieldObjects[$field->getName()]))
            trigger_error("Field ". $field->getName() ." is already defined", E_USER_WARNING);
        else {
            $this->fieldObjects[ $field->getName() ] = $field;
        }
        return $this;
    }

    /**
     * @param $fieldOrName
     * @return static
     */
    public function removeField($fieldOrName) {
        if($fieldOrName instanceof FieldInterface) {
            $fieldOrName = $fieldOrName->getName();
        }
        if(isset($this->fieldObjects[ $fieldOrName ]))
            unset($this->fieldObjects[ $fieldOrName ]);
        return $this;
    }
}