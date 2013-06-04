<?php
/*
 * The main executable for thunar-web.
 *
 * Copyright 2013 - Andrew Kester.  Distributed under GNU-GPL
 *
 * This file is part of thunar-web.
 *
 * This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require 'include/thunar-web.php';

$pbr = new phpBrowser(True);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">    
    <head>
        <title><?php echo $pbr -> getPageTitle($_SERVER['REQUEST_URI']); ?></title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body>
        <?php
        /* If we passed sort params, pass them along */
        if (isset($_GET['sort']) && isset($_GET['asc']))
            $pbr -> printTable('.', $_GET['sort'], $_GET['asc']);
        else
            $pbr -> printTable('.', 'name', 1);

        echo '<p>' . $pbr -> getPageFooter() . '</p>';
        ?>
    </body>


</html>
