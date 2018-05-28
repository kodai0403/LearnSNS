<?php 
    session_start();

    //$_SESSION変数の破棄(ローカル)
    $_SESSION = array();

    //セッションを破棄(サーバー)
    session_destroy();

    //sign.phpへ移動
    header("Location: signin.php");
    exit();
 ?>