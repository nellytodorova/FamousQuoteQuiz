<?php
if (is_array($menuNavigationLinksArr) && !empty($menuNavigationLinksArr)) {
    echo '<ul id="menu">';

    $links = '';
    foreach ($menuNavigationLinksArr as $linkId => $details) {
        $links .= '<li><a href="./' . $details['uri'] . '">' . $details['title'] . '</a></li>';
    }

    echo $links;
    echo '</ul>';
}
?>