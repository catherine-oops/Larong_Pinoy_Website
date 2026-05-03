<?php
$q = urlencode(trim($_GET['q'] ?? ''));
redirect('/larong_pinoy/games/list.php?q=' . $q);
