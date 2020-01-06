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


class Field implements FieldInterface
{
    /** @var string */
    private $name;
    /** string */
    private $valueType;
    /** @var int */
    private $length;
    /** @var mixed */
    private $defaultValue;
    /** @var int */
    private $attributes;

    public static $defaultAttributes = self::ATTR_ALLOWS_NULL;

    /**
     * Field constructor.
     * @param string $name
     * @param bool $optional
     * @param $valueType
     * @param int $length
     * @param bool $allowsNull
     * @param bool $hasDefaultValue
     * @param mixed $defaultValue
     */
    public function __construct(string $name, $valueType = self::TYPE_TEXT, int $length = 0, int $attributes = 0, $defaultValue = NULL)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;

        if($attributes & self::ATTR_INDEX)
            $attributes |= self::ATTR_AUTO_INCREMENT;

        if($attributes & self::ATTR_AUTO_INCREMENT)
            $valueType = self::TYPE_INTEGER;

        if($valueType != self::TYPE_STRING)
            $length = 0;

        $this->valueType = $valueType;
        $this->length = $length;

        $this->attributes = $attributes;
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
        return $this->attributes & self::ATTR_OPTIONAL ? true : false;
    }

    /**
     * @return mixed
     */
    public function getValueType(): string
    {
        return $this->valueType;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function allowsNull(): bool
    {
        return $this->attributes & self::ATTR_ALLOWS_NULL ? true : false;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->attributes & self::ATTR_HAS_DEFAULT ? true : false;
    }

    /**
     * @return int
     */
    public function getAttributes(): int
    {
        return $this->attributes;
    }

    /**
     * @return bool
     */
    public function isIndexed(): bool {
        return $this->attributes & self::ATTR_INDEX ? true : false;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool {
        return $this->attributes & self::ATTR_UNIQUE ? true : false;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement(): bool {
        return $this->attributes & self::ATTR_AUTO_INCREMENT ? true : false;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}