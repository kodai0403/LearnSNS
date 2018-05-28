<?php 
//session変数を使えるようにする
session_start();

//DBに接続
require("dbconnect.php");

//feed_idを取得
$feed_id = $_GET["feed_id"];

//SQL文を作成する(INSERT文)
$sql = "DELETE FROM `likes` WHERE `feed_id` = ?";

//SQL実行
$data = array($feed_id);
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

//一覧に戻る
header("Location: timeline.php");


 ?>


