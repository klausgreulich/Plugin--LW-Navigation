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

/**
 * Das lw_pagetree Plugin
 *
 * @author      Dr. Andreas Eckhoff
 * @copyright   Copyright &copy; 2005 Logic Works GmbH
 * @package     LW Contentory
 */
class lw_navigation extends lw_object {

    public function __construct($pid=false)
    {
        $reg = lw_registry::getInstance();
        $this->config = $reg->getEntry("config");
        $this->auth = $reg->getEntry("auth");
        $this->pid = $pid;
        include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_dh.class.php");
        $this->dh = new lw_navigation_dh($this->pid);
    }

    /*
      Parameter können sein:
      type = bc, list, tree, treelayer
      bei bc:
      divider = Trennzeichen (opt, standard = |)
      startlevel = Ebene ab der die Brotkrumennavi anfangen soll
      bei list:
      sistersof = liste der Seiten, die Geschwister von Seite x sind (optional, standard = aktuelle Seite)
      childrenof = liste der Seiten, die der angegebenen Seite untergeordnet sind (optional, hat Vorrang vor sisterof)
      bei tree:
      startpage = Seite ab der die Navigation starten soll.
      startlevel = Ebene ab der die Navigation angezeigt werden soll
      bei treelayer:
      layer = Anzahl der Ebenen die von der aktuellen Seite aus nach oben hin angezeigt werden sollen (opt. wenn nicht angegeben, dann automatisch auf 1 gesetzt)
      subpage = 1|0 sollen die Unterseiten der aktuellen Seite angezeigt werden?
     */

    public function setParameter($param)
    {
        $parts = explode("&", $param);
        foreach ($parts as $part) {
            $sub = explode("=", $part);
            $this->params[$sub[0]] = $sub[1];
        }
    }

    public function buildPageOutput()
    {
        switch ($this->params["type"]) {
            case "list":
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_list.class.php");
                $object = new lw_navigation_list();
                break;

            case "step":
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_step.class.php");
                $object = new lw_navigation_step();
                break;

            case "breadcrumb":
            case "bc":
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_bc.class.php");
                $object = new lw_navigation_bc();
                break;

            case "dropdown":
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_dropdown.class.php");
                $object = new lw_navigation_dropdown();
                break;

            case "treelayer":
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_treelayer.class.php");
                $object = new lw_navigation_treelayer();
                break;

            case "opentree":
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_opentree.class.php");
                $object = new lw_navigation_opentree();
                break;

            case "tree":
            default:
                include_once($this->config['plugin_path']['lw'] . "lw_navigation/lw_navigation_tree.class.php");
                $object = new lw_navigation_tree();
                break;
        }
        $object->setDelegate($this);
        return $object->getOutput();
    }

    public function isRoleAllowed($roleid)
    {
        if (!$roleid) {
            return true;
        }
        if ($this->auth->getUserdata("role_id") == $roleid) {
            return true;
        }
        return false;
    }

    public function isPageAllowed($pageid)
    {
        if ($this->auth->isInPages($pageid)) {
            return true;
        }
        return false;
    }

    public function buildNavigationLink($array, $prefix=false)
    {
        if (strlen(trim($array['redirect'])) > 0) {
            $link = trim($array['redirect']);
        } elseif (strlen(trim($array['urlname'])) > 0) {
            $link = $this->config['url']['client'] . trim($array['urlname']);
        } else {
            $link = $this->config['url']['client'] . "index.php?index=" . $array['id'];
        }

        $out = "<a href=\"" . $link . "\"";
        if (strlen($array['class']) > 0)
            $out.=" class=\"" . $array['class'] . "\"";
        $out.="><span>" . $prefix . $array['name'] . "</span></a>";
        return $out;
    }

    function isBasePage($id)
    {
        $temp = explode(",", $this->config['custom']['noeditallowed']);
        foreach ($temp as $entry) {
            if ($entry == $id) {
                return true;
            }
        }
        return false;
    }

}