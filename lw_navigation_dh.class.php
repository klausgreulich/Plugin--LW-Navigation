<?php

/* * ************************************************************************
 *  Copyright notice
 *
 *  Copyright 1998-2009 Logic Works GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *  
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *  
 * ************************************************************************* */

class lw_navigation_dh extends lw_object {

    public function __construct($pid=false)
    {
        $reg = lw_registry::getInstance();
        $this->config = $reg->getEntry("config");
        $this->db = $reg->getEntry("db");
        $this->pid = $pid;

        $sql = "SELECT * FROM " . $this->config['dbt']['pages'] . " WHERE id = " . $this->pid;
        $this->pageData = $this->db->select1($sql);
        $this->pageData['path'] = $this->pageData['path'] . ":" . $this->pid . ":";
    }

    public function getAllSisters($id = false)
    {
        if (strlen(trim($id)) < 1) {
            $id = $this->pageData['relation'];
        } else {
            $sql = "SELECT * FROM " . $this->config['dbt']['pages'] . " WHERE id = " . $id;
            $temp = $this->db->select1($sql);
            $id = $temp['relation'];
        }
        $sql = "SELECT * FROM " . $this->config['dbt']['pages'] . " WHERE relation = " . $id . " ORDER BY seq";
        return $this->db->select($sql);
    }

    public function getAllChildren($id = false)
    {
        if (strlen(trim($id)) < 1) {
            return false;
        }
        $sql = "SELECT * FROM " . $this->config['dbt']['pages'] . " WHERE relation = " . $id . " ORDER BY seq";
        return $this->db->select($sql);
    }

    public function getBreadcrumb($startlevel=1)
    {
        $startlevel++;
        $parts = explode(":", $this->pageData['path']);
        $where.= " id = '" . $this->pid . "' ";
        foreach ($parts as $page) {
            if (strlen(trim($page)) > 0 && $i > $startlevel) {
                $where.= " OR id = '" . $page . "' ";
            }
            $i++;
        }
        if (strlen($where) > 0) {
            $where = " ( " . $where . " ) AND ";
        }

        $sql = "SELECT * FROM " . $this->config['dbt']['pages'] . " WHERE " . $where . " 1=1 ORDER BY path";
        return $this->db->select($sql);
    }

    public function getAllPages()
    {
        $sql = "SELECT id, name, relation, title, urlname, redirect, path, intranet, nonav, disabled FROM " . $this->config['dbt']['pages'] . " ORDER BY seq";
        return $this->db->select($sql);
    }

    public function isInActualPath($id)
    {
        if (strstr($this->pageData['path'], ":" . $id . ":")) {
            return true;
        } else {
            return false;
        }
    }

    public function getActualPathArray()
    {
        $parts = explode(":", $this->pageData['path']);
        foreach ($parts as $page) {
            if (strlen(trim($page)) > 0) {
                $array[] = $page;
            }
        }
        return $array;
    }

}