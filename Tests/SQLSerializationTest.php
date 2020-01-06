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

/**
 * SQLSerializationTest.php
 * skyline-pdo
 *
 * Created on 2020-01-06 17:38 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\PDO\Compiler\Structure\SQL\MySQL;
use Skyline\PDO\Compiler\Structure\SQL\SQLite;
use Skyline\PDO\Compiler\Structure\Table\Field;
use Skyline\PDO\Compiler\Structure\Table\Table;

class SQLSerializationTest extends TestCase
{
    public function testMySQLSerializer() {
        $mySQL = new MySQL();
        $sqlite = new SQLite();

        $this->assertEquals("CREATE TABLE TEST (
\tid INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT
);",
            $mySQL->serializeTable(
                (new Table("TEST"))
                    ->addField( new Field("id", Field::TYPE_INTEGER, 0, Field::ATTR_INDEX) )
            )
        );

        $this->assertEquals("CREATE TABLE IF NOT EXISTS TEST (
\tid INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT
);",
            $mySQL->serializeTable(
                (new Table("TEST", true))
                    ->addField( new Field("id", Field::TYPE_INTEGER, 0, Field::ATTR_INDEX) )
            )
        );

        $this->assertEquals("name VARCHAR(25) NOT NULL", $mySQL->serializeField(
            new Field("name", Field::TYPE_STRING, 25)
        ));

        $this->assertEquals("name TEXT NULL", $mySQL->serializeField(
            new Field("name", Field::TYPE_TEXT, 0, Field::ATTR_ALLOWS_NULL)
        ));

        $this->assertEquals("name INTEGER NOT NULL AUTO_INCREMENT", $mySQL->serializeField(
            new Field("name", Field::TYPE_TEXT, 0, Field::ATTR_AUTO_INCREMENT)
        ));

        $this->assertEquals("name INTEGER NOT NULL AUTOINCREMENT", $sqlite->serializeField(
            new Field("name", Field::TYPE_TEXT, 0, Field::ATTR_AUTO_INCREMENT)
        ));

        $this->assertEquals("name INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT", $mySQL->serializeField(
            new Field("name", Field::TYPE_DATE, 0, Field::ATTR_INDEX)
        ));

        $this->assertEquals("name INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT", $sqlite->serializeField(
            new Field("name", Field::TYPE_STRING, 17, Field::ATTR_INDEX)
        ));
    }
}
