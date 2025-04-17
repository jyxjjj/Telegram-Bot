<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use Illuminate\Foundation\Console\RouteListCommand;
use Symfony\Component\Console\Input\InputOption;

class RouteList extends RouteListCommand
{
    protected $headers = [
        'Domain', 'Method', 'URI', 'Name', 'Middleware', 'Action',
    ];
    protected $verbColors = [
        'ANY' => '#FF0000',
        'GET' => '#00C79D',
        'HEAD' => '#3A93DA',
        'OPTIONS' => '#3A93DA',
        'POST' => '#E66312',
        'PUT' => '#3A93DA',
        'PATCH' => '#DC0098',
        'DELETE' => '#FF0000',
    ];

    protected function forCli($routes): array
    {
        $routes = $routes->toArray();
        $count = count($routes);
        $this->output->writeln("    <fg=#3A93DA;options=bold>Showing [$count] routes</>");
        foreach ($routes as &$route) {
            $method = $route['method'] == 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS' ? 'ANY' : $route['method'];
            $methodLen = strlen($method);
            $dots1 = str_repeat(' ', 16 - $methodLen);
            $uri = $route['domain'] ? ('https://' . $route['domain'] . '/' . ltrim($route['uri'], '/')) : $route['uri'];
            $uriLen = mb_strlen($uri);
            $dots2 = str_repeat('.', 72 - $uriLen);
            $uri = "$dots1 $uri $dots2";
            $action = $this->formatActionForCli($route);
            $action = str_replace('   ', ' › ', $action ?? '');
            $methods = explode('|', $method);
            foreach ($methods as &$m) {
                $m = sprintf('<fg=%s>%s</>', $this->verbColors[$m] ?? 'default', $m);
            }
            $method = implode('<fg=#3A93DA>|</>', $methods);
            $uri = preg_replace('#({[^}]+})#', '<fg=yellow>$1</>', $uri);
            $route = sprintf(
                '  <fg=white;options=bold>%s</> <fg=white>%s</> <fg=#3A93DA>%s</>',
                $method,
                $uri,
                $action,
            );
        }
        return $routes;
    }

    protected function getOptions(): array
    {
        return [
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by domain'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Only show routes matching the given path pattern'],
            ['except-path', null, InputOption::VALUE_OPTIONAL, 'Do not display the routes matching the given path pattern'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action, middleware) to sort by', 'domain'],
            ['except-vendor', null, InputOption::VALUE_NONE, 'Do not display routes defined by vendor packages'],
            ['only-vendor', null, InputOption::VALUE_NONE, 'Only display routes defined by vendor packages'],
        ];
    }

    protected function getRoutes(): array
    {
        $routes = collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();
        $sort = $this->option('sort');
        $routes = $this->sortRoutes($sort !== null ? $sort : 'domain', $routes);
        if ($this->option('reverse')) {
            $routes = array_reverse($routes);
        }
        return $this->pluckColumns($routes);
    }
}
