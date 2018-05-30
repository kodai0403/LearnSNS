
<?php
  session_start();
  // require(dbconnect)
  require('dbconnect.php');
  // SELECT usersテーブルから $_SESSIONに保存されているidを使って一件だけ取り出す。
      $sql = 'SELECT * FROM `users` WHERE `id`=?';
      $data = array($_SESSION['id']);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);

      $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);
  //$signin_userに取り出したレコードを代入する
  //写真と名前をレコードから取り出す
  //$img_name に写真のファイル名を代入する。
  //$name に名前を代入する

      // var_dump($signin_user);
      // echo $signin_user['name'];//ユーザー名
      // echo $signin_user['img_name'];//画像ファイル名

      //初期化をする
      $errors = array();
      //$_POSTがどこからきているのかが良くわかってない。
      //ボタンを押したときにしか、この処理を行わないよ
      if(!empty($_POST)){
        $feed = $_POST['feed'];
            if($feed != ''){
              //空じゃない時の投稿処理
              //SQL文の実行
            $sql = 'INSERT INTO `feeds` SET `feed`=?, `user_id`=?,`created`=NOW()';
            $data = array($feed, $signin_user['id'],);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
 
            header('Location: timeline.php');
            exit();

            }
            else{
              //空の時 エラー処理
              $errors['feed'] = 'blank';
            }
      }

      $page = '';  //ページ番号が入る変数を準備する
      $page_row_number = 5; //1ページあたりに表示するデータの数

      if (isset($_GET['page'])) {
        $page = $_GET['page'];
      }else{
        //get送信されているページがない場合、１ページ目とみなす
        $page = 1;
      }

      //データを取得する開始番号を計算
      $start = ($page -1)*$page_row_number;

    //検索ボタンが押されたら、曖昧検索
    //検索ボタンが押された=GET送信されたsearch_wordというキーのデータがある
      if(isset($_GET['search_word']) == true){
        //あいまい検索用SQL
         $sql = 'SELECT `feeds`.*,`users`.`name`,`users`.`img_name` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id`=`users`.`id` WHERE `feeds`.`feed` LIKE  "%'.$_GET['search_word'].'%" ORDER BY id DESC';
      }else{
        // 通常(検索ボタンを押していない)は全件取得
        //LEFT JOIN で全件取得
        $sql = "SELECT `feeds`.*,`users`.`name`,`users`.`img_name` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id`=`users`.`id` WHERE 1 ORDER BY id DESC LIMIT $start,$page_row_number";
      }
    
    $data = array();
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    $feeds = array();
    // $arr = array();
    while (true) {
        $record = $stmt->fetch(PDO::FETCH_ASSOC); //  ここより上でfetchしてない？
        if ($record == false) {
            break;
        }

        //like数を取得するSQL文を作成する
        $like_sql = "SELECT COUNT(*) AS `like_cnt` FROM `likes` WHERE `feed_id` = ?";

        $like_data = array($record["id"]);


        //SQL文を実行する
        $like_stmt = $dbh->prepare($like_sql);
        $like_stmt->execute($like_data);

        //like数を取得する
        $like = $like_stmt->fetch(PDO::FETCH_ASSOC);
        //$like = array("like_cnt"=>5);


        $record['like_cnt'] = $like["like_cnt"];

        //like済みか判断するsqlを作成する
        $like_flag_sql = "SELECT COUNT(*) AS `like_flag` FROM `likes` WHERE `user_id` = ? AND `feed_id` = ?";

        $like_flag_data = array($_SESSION["id"],$record["id"]);

        //sqlを実行する
        $like_flag_stmt = $dbh->prepare($like_flag_sql);
        $like_flag_stmt->execute($like_flag_data);

        //likeしている数を取得する
        $like_flag = $like_flag_stmt->fetch(PDO::FETCH_ASSOC);



        if ($like_flag["like_flag"]>0) {
          $record["like_flag"] = 1;
        }else{
          $record["like_flag"] = 0;
        }

        //いいね済みのみのリンクが押された時には、配列にすでにいいね！しているものだけを代入する
        if (isset($_GET["feed_select"]) && ($_GET["feed_select"] == "likes") && ($record["like_flag"] == 1)) {$feeds[] = $record;
          # code...
        }
        //feed_selectが指定されていない時は全練表示をする
        if (!isset($_GET["feed_select"])) {
          $feeds[] = $record;
          # code...
        }

        if (isset($_GET["feed_select"]) &&($_GET["feed_select"] == "news")) {
          $feeds[] = $record;
        }
        // $feeds[] = $record;
        // $arr[] = 'ほげ';
    }

    // echo '<pre>';
    // echo '</pre>';

    // // $c = count($feeds);

    // for ($i=0; $i < $c; $i++) {
    //     echo $feeds[$i]['feed'];
    //     echo '<br>';
    // }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px; background: #E4E6EB;">
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Learn SNS</a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse1">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">タイムライン</a></li>
          <li><a href="user_index.php">ユーザー一覧</a></li>
        </ul>
        <form method="GET" action="" class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" name="search_word" class="form-control" placeholder="投稿を検索">
          </div>
          <button type="submit" class="btn btn-default">検索</button>
        </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $signin_user['img_name']; ?>" width="18" class="img-circle"><?php echo $signin_user['name']; ?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-xs-3">
        <ul class="nav nav-pills nav-stacked">
          <?php if (isset($_GET["feed_select"]) && ($_GET["feed_select"]=="likes")) {?>
          <li><a href="timeline.php?feed_select=news">新着順</a></li>
          <li class="active"><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
          <!-- <li><a href="timeline.php?feed_select=follows">フォロー</a></li> -->
          <?php }else{ ?>
          <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
          <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
          <?php } ?>
        </ul>
      </div>
      <div class="col-xs-9">
        <div class="feed_form thumbnail">
          <form method="POST" action="">
            <div class="form-group">
              <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
            <?php if(isset($errors['feed']) && $errors['feed'] == 'blank'){ ?>
            <p class = "nav nav-pills nav-stacked">投稿データを入力してください</p>
            <?php } ?>
            </div>
            <input type="submit" value="投稿する" class="btn btn-primary">
          </form>
        </div>
          <?php foreach ($feeds as $feed) { ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40">
              </div>
              <div class="col-xs-11">
                <?php echo $feed['name']; ?><br>
                <a href="#" style="color: #7F7F7F;"><?php echo $feed['created']; ?></a>
              </div>
            </div>
            <div class="row feed_content">
              <div class="col-xs-12" >
                <span style="font-size: 24px;"><?php echo $feed['feed']; ?></span>
              </div>
            </div>
            <div class="row feed_sub">
              <div class="col-xs-12">

                    <?php if($feed["like_flag"] == 0){ ?>
                    <a href="like.php?feed_id=<?php echo $feed["id"] ?>">  
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！</button>
                    </a>
                    <?php }else{ ?>
                    <a href="trush.php?feed_id=<?php echo $feed["id"] ?>">  
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-down" aria-hidden="true"></i>いいねを取り消す</button>
                    </a>
                    <?php } ?>
                <?php if ($feed["like_cnt"] > 0){ ?>
                <span class="like_count">いいね数 : <?php echo $feed['like_cnt']; ?></span>
                <?php } ?>
                <span class="comment_count">コメント数 : 9</span>
                  <?php if($feed["user_id"] == $_SESSION["id"]){ ?>
                  <a href="edit.php?feed_id=<?php echo $feed["id"] ?>" class="btn btn-success btn-xs">編集</a>
                  <a onclick = "return confirm('本当に消すの?');"href="delete.php?feed_id=<?php echo $feed["id"] ?>" class="btn btn-danger btn-xs">削除</a>
                  <?php } ?>
              </div>
            </div>
          </div>
          <?php } ?>
        <div aria-label="Page navigation">
          <ul class="pager">
            <?php if ($page==1){?>
              <li class="previous disabled"><a href="timeline.php?page=<?php echo $page-1; ?>"><span aria-hidden="true">&larr;</span> Newer</a></li>
            <?php }else{ ?>
            <li class="previous"><a href="timeline.php?page=<?php echo $page-1; ?>"><span aria-hidden="true">&larr;</span> Newer</a></li>
            <?php } ?>
            <li class="next"><a href="timeline.php?page=<?php echo $page+1; ?>">Older <span aria-hidden="true">&rarr;</span></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>

