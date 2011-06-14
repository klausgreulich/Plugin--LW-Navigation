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

class lw_navigation_treelayer extends lw_object {

    public function __construct()
    {
        
    }

    public function setDelegate($obj)
    {
        $this->delegate = $obj;
    }

    public function getOutput()
    {
        if (intval($this->delegate->params['layer']) < 1) {
            $this->layer = 1;
        } else {
            $this->layer = intval($this->delegate->params['layer']);
        }
        $this->subpage = $this->delegate->params['subpage'];

        $this->preparePageArray();
        $out.="<div class=\"lwn_treewrapper\">\n";
        foreach ($this->pagearray as $page) {
            if ($page['intranet'] == 1) {
                $auth = lw_in_auth::getInstance();
                $allowed = $auth->isObjectAllowed('page', $page['id']);
            } else {
                $allowed = true;
            }
            if ($allowed == true && $page['nonav'] != 1 && $page['disabled'] != 1) {
                if ($page['id'] == $this->delegate->pid) {
                    $class = " lwn_actual";
                } elseif ($this->delegate->dh->isInActualPath($page['id'])) {
                    $class = " lwn_inpath";
                } else {
                    $class = "";
                }
                $out.="    <div class=\"lwn_tree_lvl" . ($page['level']) . $class . "\" id=\"lwn_page_" . $page['id'] . "\">" . $this->delegate->buildNavigationLink($page) . "</div>\n";
            }
        }
        $out.="</div>\n";
        return $out;
    }

    public function preparePageArray()
    {
        $patharray = $this->delegate->dh->getActualPathArray();
        $sub = array_slice($patharray, ($this->layer * -1), $this->layer);
        $array = $this->delegate->dh->getAllSisters($sub[0]);
        foreach ($array as $pages) {
            $pages['level'] = 1;
            $this->pagearray[] = $pages;
            if ($this->delegate->dh->isInActualPath($pages['id']) && $pages['id'] != $this->delegate->pid) {
                $this->buildPageArray(1, $pages['id']);
            }
            if ($pages['id'] == $this->delegate->pid && $this->subpage == 1) {
                $this->buildPageArray(1, $pages['id']);
            }
        }
    }

    public function buildPageArray($deep, $startid)
    {
        $deep++;
        $array = $this->delegate->dh->getAllChildren($startid);
        foreach ($array as $pages) {
            $pages['level'] = $deep;
            $this->pagearray[] = $pages;
            if ($this->delegate->dh->isInActualPath($pages['id']) && $pages['id'] != $this->delegate->pid) {
                $this->buildPageArray($deep, $pages['id']);
            }
            if ($pages['id'] == $this->delegate->pid && $this->subpage == 1) {
                $this->buildPageArray($deep, $pages['id']);
            }
        }
    }

}