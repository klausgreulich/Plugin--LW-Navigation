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

class lw_navigation_list extends lw_object {

    public function __construct($pid=false)
    {
        
    }

    public function setDelegate($obj)
    {
        $this->delegate = $obj;
    }

    public function getOutput()
    {
        if ($this->delegate->params['layer'] > 0) {
            $parts = explode(":", $this->delegate->dh->pageData['path']);
            foreach ($parts as $part) {
                if (strlen(trim($part)) > 0) {
                    $elm[] = $part;
                }
            }
            if ($elm[$this->delegate->params['layer']] > 0) {
                $pages = $this->delegate->dh->getAllSisters($elm[$this->delegate->params['layer']]);
            } elseif ($this->delegate->pid == $elm[$this->delegate->params['layer']]) {
                //$pages = $this->delegate->dh->getAllChildren($this->delegate->pid);
            } else {
                $pages = $this->delegate->dh->getAllChildren($this->delegate->pid);
            }
            if (count($pages) < 1)
                return false;
        } elseif ($this->delegate->params['childrenof']) {
            $pages = $this->delegate->dh->getAllChildren($this->delegate->params['childrenof']);
        } else {
            $pages = $this->delegate->dh->getAllSisters($this->delegate->params['sistersof']);
        }

        $out.="<ul class=\"lwn_list\">\n";
        foreach ($pages as $page) {
            if ($page['intranet'] == 1) {
                $auth = lw_in_auth::getInstance();
                $allowed = $auth->isObjectAllowed('page', $page['id']);
            } else {
                $allowed = true;
            }
            if ($page['id'] == $this->delegate->pid) {
                $class = " lwn_actual";
            } elseif ($this->delegate->dh->isInActualPath($page['id'])) {
                $class = " lwn_inpath";
            } else {
                $class = "";
            }
            if ($page['nonav'] != 1 && !$page['disabled'] && $allowed == true) {
                $out = str_replace(" lw_laslisttitem", "", $out);
                $out.="    <li class=\"lwn_item" . $class . " lw_laslisttitem\" id=\"lwn_page_" . $page['id'] . "\">" . $this->delegate->buildNavigationLink($page) . "</li>\n";
            }
        }
        $out.="</ul>\n";
        return $out;
    }

}