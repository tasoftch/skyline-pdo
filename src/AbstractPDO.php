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

namespace Skyline\PDO;


use Skyline\Kernel\ExposeClassInterface;
use TASoft\Util\Mapper\DateMapper;
use TASoft\Util\Mapper\MapperChain;
use TASoft\Util\PDO;

abstract class AbstractPDO extends PDO implements ExposeClassInterface
{
    private $configuration;

    const ARGUMENT_USERNAME = 'username';
    const ARGUMENT_PASSWORD = 'password';

    const CONFIG_TABLE_PREFIX = 'prefix';

    public function setConfiguration($config) {
        $this->configuration = $config;
    }

    public function __construct($dsn, $username = NULL, $passwd = NULL, $options = NULL)
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $tm = new MapperChain();
        $this->setTypeMapper($tm);
        $this->adjustTypeMapping($tm);
    }

    protected function adjustTypeMapping(MapperChain $chain) {
        $chain->addMapper(new DateMapper());
    }

    protected function resolveSQLTablePrefix(string $sql): string {
        $prefix = $this->configuration[ static::CONFIG_TABLE_PREFIX ] ?? 'SKY_';

        $sql = preg_replace("/SKY_([A-Z_]+)/", "$prefix$1", $sql);

        return $sql;
    }

    public function query($statement, $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args): \PDOStatement|false
    {
        if(is_string($statement))
            $statement = $this->resolveSQLTablePrefix($statement);

        return parent::query($statement, $mode, ...$fetch_mode_args);
    }

    public function exec($statement): int|false
    {
        if(is_string($statement))
            $statement = $this->resolveSQLTablePrefix($statement);
        parent::exec($statement);
    }

    public function prepare($statement, $driver_options = []): \PDOStatement|false
    {
        if(is_string($statement))
            $statement = $this->resolveSQLTablePrefix($statement);
        return parent::prepare($statement, $driver_options);
    }


    public function select(string $sql, array $arguments = [])
    {
        $sql = $this->resolveSQLTablePrefix($sql);
        return parent::select($sql, $arguments);
    }

    public function selectWithObjects(string $sql, array $arguments = [])
    {
        $sql = $this->resolveSQLTablePrefix($sql);
        return parent::selectWithObjects($sql, $arguments);
    }

    public function injectWithObjects(string $sql)
    {
        $sql = $this->resolveSQLTablePrefix($sql);
        return parent::injectWithObjects($sql);
    }

    public function inject(string $sql)
    {
        $sql = $this->resolveSQLTablePrefix($sql);
        return parent::inject($sql);
    }

    public function count(string $sql, array $arguments = []): int
    {
        $sql = $this->resolveSQLTablePrefix($sql);
        return parent::count($sql, $arguments);
    }

	public static function getPurposes(): array
	{
		return ['PDO'];
	}
}