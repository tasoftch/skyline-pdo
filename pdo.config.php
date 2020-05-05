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

use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\PDO\AbstractPDO;
use Skyline\PDO\Config\PDOFactory;
use Skyline\PDO\MySQL;
use Skyline\PDO\SQLite;
use TASoft\Service\Config\AbstractFileConfiguration;

return [
    MainKernelConfig::CONFIG_SERVICES => [
        PDOFactory::PDO_SERVICE => [
            AbstractFileConfiguration::SERVICE_CONTAINER => PDOFactory::class,
            AbstractFileConfiguration::SERVICE_INIT_ARGUMENTS => [
                'defaultPDO' => '%pdo.primary%',
                'alternatePDO' => '%pdo.secondary%',
            ],
			AbstractFileConfiguration::CONFIG_SERVICE_TYPE_KEY => AbstractPDO::class
        ],

        MySQL::SERVICE_NAME => [
            AbstractFileConfiguration::SERVICE_CLASS => MySQL::class,
            AbstractFileConfiguration::SERVICE_INIT_ARGUMENTS => [
                MySQL::ARGUMENT_HOST => '%pdo.mysql.host%',
                MySQL::ARGUMENT_DATA_BASE => '%pdo.mysql.dataBase%',
                MySQL::ARGUMENT_USERNAME => '%pdo.mysql.username%',
                MySQL::ARGUMENT_PASSWORD => '%pdo.mysql.password%',
                MySQL::ARGUMENT_SOCKET => '%pdo.mysql.socket%',
				MySQL::ARGUMENT_VERIFIED => '%pdo.mysql.verified%'
            ],
            AbstractFileConfiguration::SERVICE_INIT_CONFIGURATION => [
                MySQL::CONFIG_TABLE_PREFIX => '%pdo.prefix%'
            ]
        ],

        SQLite::SERVICE_NAME => [
            AbstractFileConfiguration::SERVICE_CLASS => SQLite::class,
            AbstractFileConfiguration::SERVICE_INIT_ARGUMENTS => [
                SQLite::ARGUMENT_FILENAME => '%pdo.sqlite.filename%',
                SQLite::ARGUMENT_USERNAME => '%pdo.sqlite.username%',
                SQLite::ARGUMENT_PASSWORD => '%pdo.sqlite.password%',
            ],
            AbstractFileConfiguration::SERVICE_INIT_CONFIGURATION => [
                SQLite::CONFIG_TABLE_PREFIX => '%pdo.prefix%'
            ]
        ]
    ]
];
