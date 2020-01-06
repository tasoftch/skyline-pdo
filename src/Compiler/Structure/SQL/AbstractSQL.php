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

namespace Skyline\PDO\Compiler\Structure\SQL;


use Skyline\PDO\Compiler\Structure\Table\FieldInterface;
use Skyline\PDO\Compiler\Structure\Table\TableInterface;

abstract class AbstractSQL implements SerializerInterface
{
    /** @var \PDO */
    private $PDO;

    /**
     * AbstractSQL constructor.
     * @param \PDO $PDO
     */
    public function __construct(\PDO $PDO = NULL)
    {
        $this->PDO = $PDO;
    }

    public function quote($expression, $type = \PDO::PARAM_STR) {
        if($this->PDO)
            return $this->PDO->quote($expression, $type);
        return var_export($expression, true);
    }

    public function serializeTable(TableInterface $table): string
    {
        if($table->isOptional())
            $SQL = sprintf("CREATE TABLE IF NOT EXISTS %s (\n", $table->getName());
        else
            $SQL = sprintf("CREATE TABLE %s (\n", $table->getName());
        $fields = [];

        foreach($table->getFieldObjects() as $field) {
            $fields[] = "\t" . $this->serializeField( $field );
        }

        $SQL .= implode(",\n", $fields) . "\n";

        return $SQL . ");";
    }

    public function serializeField(FieldInterface $field): string
    {
        $SQL = $field->getName();
        $SQL .= " " . $this->serializeFieldType($field);

        $attributes = $field->getAttributes();
        $SQL .= " " . $this->serializeAttributes($field);

        return $SQL;
    }

    protected function serializeFieldType(FieldInterface $field): string {
        return $field->getLength() ? sprintf("%s(%d)", $field->getValueType(), $field->getLength()) : sprintf("%s", $field->getValueType());
    }

    protected function serializeAttributes(FieldInterface $field): string {
        $attrs = [];
        $attributes = $field->getAttributes();

        $attrs[] = $this->serializeAttributeNULL( $attributes & FieldInterface::ATTR_ALLOWS_NULL ? true : false );

        for($e = 1;$e <= $attributes;$e<<=1) {
            if($e == FieldInterface::ATTR_HAS_DEFAULT && $attributes & $e)
                $attrs[] = $this->serializeAttributeDefaultValue( $field );
            elseif($e == FieldInterface::ATTR_INDEX && $attributes & $e)
                $attrs[] = $this->serializeAttributeINDEX( $field );
            elseif($e == FieldInterface::ATTR_UNIQUE && $attributes & $e)
                $attrs[] = $this->serializeAttributeUNIQUE( $field );
            elseif($e == FieldInterface::ATTR_AUTO_INCREMENT && $attributes & $e)
                $attrs[] = $this->serializeAttributeAUTO_INC( $field );
            elseif($e == FieldInterface::ATTR_UPDATE_TIME_STAMP && $attributes & $e)
                $attrs[] = $this->serializeAttributeON_UPDATE_TS( $field );
        }
        return implode(" ", $attrs);
    }


    protected function serializeAttributeNULL($flag): string {
        return $flag ? "NULL" : "NOT NULL";
    }

    protected function serializeAttributeDefaultValue(FieldInterface $field): string {
        $def = $field->getDefaultValue();
        $SQL = "";

        if($field->getAttributes() & FieldInterface::ATTR_DEFAULT_TIMESTAMP) {
            $SQL .= " DEFAULT CURRENT_TIMESTAMP";
        } elseif(is_null($def))
            $SQL .= " DEFAULT NULL";
        elseif(is_int($def)) {
            $SQL .= " DEFAULT $def";
        } elseif(is_bool($def))
            $SQL .= " DEFAULT " . ($def?'TRUE':'FALSE');
        else
            $SQL .= " DEFAULT " . $this->quote($def);
        return $SQL;
    }

    protected function serializeAttributeINDEX(FieldInterface $field): string {
        return "PRIMARY KEY";
    }

    protected function serializeAttributeUNIQUE(FieldInterface $field): string {
        return "UNIQUE";
    }

    protected function serializeAttributeAUTO_INC(FieldInterface $field): string {
        return "AUTO_INCREMENT";
    }

    protected function serializeAttributeON_UPDATE_TS(FieldInterface $field): string {
        return "";
    }

    public function serializeContentRow(array $record): string
    {
        $names = [];
        $values = [];

        foreach($record as $name => $value) {
            $names[] = "`$name`";
            $values[] = $this->quote($value);
        }

        $names = implode(",", $names);
        $values = implode(",", $values);

        return "($names) VALUES ($values)";
    }
}