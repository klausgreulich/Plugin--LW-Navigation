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

class lw_navigation_tree extends lw_object {

    public function __construct()
    {
        
    }

    public function setDelegate($obj)
    {
        $this->delegate = $obj;
        if ($this->delegate->params['startlevel']) {
            $this->startlevel = $this->delegate->params['startlevel'];
        } else {
            $this->startlevel = 6;
        }
    }
    
    public function isPageAllowed($pageid)
    {
        $auth = lw_in_auth::getInstance();
        if ($auth->isObjectAllowed('page', $pageid))
        {
            return true;
        }
        if ( lw_registry::getInstance()->getEntry("auth")->isInPages($pageid) )
        {
            return true;
        }
        return false;
    }  

    public function getOutput()
    {
        $flag = false;
        if ($this->delegate->params['startpage']) {
            $gate = 1;
        } else {
            $gate = 0;
        }

        $this->preparePageArray();

        $level = 0;
        for ($i = 0; $i < count($this->array); $i++) {
            if ($this->array[$i]['id'] == $this->delegate->params['startpage']) {
                $gate = 0;
                $levelmodifier = $this->array[$i]['level'] - 1;
            }
            $allowed = $this->isPageAllowed($i);
            if ($gate == 0 && $allowed == true && (strstr($this->array[$i]['path'], ":" . $this->delegate->params['startpage'] . ":"))) {
                if ($this->array[$i]['id'] == $this->delegate->pid) {
                    $class = "active";
                } elseif ($this->delegate->dh->isInActualPath($this->array[$i]['id'])) {
                    $class = "inpath";
                } else {
                    $class = "";
                }

                $level = count(explode(":", $this->array[$i]['path']));
                if ($level > $this->startlevel) {
                    $flag = true;
                    $this->array[$i]['class'] = $class;
                    $currentlevel = $this->array[$i]['level'] - $levelmodifier;
                    $nextlevel = $this->array[$i + 1]['level'] - $levelmodifier;

                    $out.="    <li id=\"page_" . $this->array[$i]['id'] . "\">" . $this->delegate->buildNavigationLink($this->array[$i]);
                    if ($nextlevel > $currentlevel && $this->isPageAllowed($i+1)) {
                        $out.= "<ul>";
                        $open++;
                    }
                    if ($nextlevel < $currentlevel && $open < 1) {
                        $out.= "</li>\n";
                    }
                    if ($nextlevel < $currentlevel && $open > 0) {
                        $out.= "</li>\n</ul>\n</li>\n";
                        $open--;
                    }
                    if ($nextlevel == $currentlevel) {
                        $out.= " </li>\n";
                    }
                    $diff = $currentlevel - $nextlevel;
                    if ($diff > 1) {
                        $jk=1;
                        while ($jk < $diff) {
                            if ($open > 0) {
                                $out.= "</ul>\n</li>\n";
                                $open--;
                            }
                            $jk++;
                        }
                    }
                }
            }
        }
        if ($flag == false) {
            return false;
        } else {
            return "<ul>" . $out . "</ul>\n" . $debugout;
        }
    }

    public function preparePageArray()
    {
        $level = 0;
        $this->pages = $this->delegate->dh->getAllPages();
        $this->pathArray = $this->delegate->dh->getActualPathArray();
        $pathID = $this->pathArray[0];
        $this->array[] = $this->getPage($pathID, $level);
        $this->getAllSubPages($pathID, $level);
    }

    protected function getAllSubPages($pathID, $level)
    {
        $level++;
        foreach ($this->pages as $page) {
            if ($page['relation'] == $pathID) {
                $page['level'] = $level;
                if (!$page['nonav'] && !$page['disabled']) {
                    $this->array[] = $page;
                    if ($this->delegate->dh->isInActualPath($page['id'])) {
                        $this->getAllSubPages($page['id'], $level);
                    }
                }
            }
        }
    }

    protected function getPage($id, $level)
    {
        foreach ($this->pages as $page) {
            if ($page['id'] == $id) {
                $page['level'] = $level;
                return $page;
            }
        }
    }

}
