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

class lw_navigation_bc extends lw_object {

    public function __construct($pid=false)
    {
        
    }

    public function setDelegate($obj)
    {
        $this->delegate = $obj;
    }

    public function getOutput()
    {
        if ($this->delegate->params['divider'] == "none") {
            $divider = "";
        } elseif ($this->delegate->params['divider'] == "gt") {
            $divider = "&gt;";
        } elseif ($this->delegate->params['divider'] == "lt") {
            $divider = "&lt;";
        } elseif ($this->delegate->params['divider'] == "space") {
            $divider = " ";
        } elseif (strlen(trim($this->delegate->params['divider'])) > 0) {
            $divider = $this->delegate->params['divider'];
        } else {
            $divider = "|";
        }
        $pages = $this->delegate->dh->getBreadcrumb($this->delegate->params['startlevel']);
        foreach ($pages as $page) {
            if ($page['intranet'] == 1) {
                $auth = lw_in_auth::getInstance();
                $allowed = $auth->isObjectAllowed('page', $page['id']);
            } else {
                $allowed = true;
            }
            if ($allowed == true && !$this->delegate->isBasePage($page["id"]) && $page['nonav'] != 1 && !$page['disabled']) {
                if (strlen(trim($out)) > 0) {
                    $out.=" " . $divider . " ";
                }
                if ($page['id'] == $this->delegate->pid) {
                    $class = " active";
                }
                $out.=$this->delegate->buildNavigationLink($page, "") . "\n";
            }
        }
        return $out;
    }

}