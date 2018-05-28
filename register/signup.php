<?php
    
    session_start();
    $errors = array();

    if (!empty($_POST)){
      //POST送信が会った時に以下を実行する
      $name = $_POST['input_name'];
      $email = $_POST['input_email'];
      $password = $_POST['input_password'];
      //countの中にパスワードの中に入っている文字列を取るためのコード
      //countは基本的に配列の数を数える
      $count = strlen($password);
      $count_name = strlen($name);

      //ユーザー名の空チェック
      if ($name == ''){
        $errors['name'] = 'blank';
      }
      elseif (4 > $count_name || 16 < $count_name) {
          $errors['name'] = 'length';
       } else{
        if ( $rec['cnt'] > 0){
          //メールアドレスの数が0よりも大きい = すでに登録がある
          //重複しているメールアドレスがあったら
          $errors['email'] = 'duplicate';
       }
      //アドレスの空チェック
      if ($email == ''){
        $errors['email'] = 'blank';
      }
      else{
        // 1.DB接続
            require('../dbconnect.php');

        // 2.SQL
            //今回は入力したメールアドレスに一致するものを取り出してきている。一致した数を数えることによって、0よりも大きかったら一致するようにする。
            $sql = 'SELECT COUNT(*) as `cnt` FROM `users` WHERE `email`=?' ;
            $data = array($email);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);

        // 3 DB切断
            $dbh = null;

        // 4 取り出し
            $rec = $stmt->fetch(PDO::FETCH_ASSOC);

            var_dump($rec);

        if ( $rec['cnt'] > 0){
          //メールアドレスの数が0よりも大きい = すでに登録がある
          //重複しているメールアドレスがあったら
          $errors['email'] = 'duplicate';
        }
      }
      //パスワードの空チェック
      if ($password == ''){
        $errors['password'] = 'blank';
      }
      elseif (4 > $count || 16 < $count) {
          $errors['password'] = 'length';
      }

      //画像を取得
      $file_name = $_FILES['input_img_name']['name'];
      echo $file_name."<br>";
      if(!empty($file_name)){
        //拡張子チェック
            $file_type = substr($file_name, -4);
            //画像の後ろから三文字を取得
            $file_type = strtolower($file_type);
            //大文字があった時に小文字に変えてくれるもの
            if( $file_type != '.jpg' && $file_type != '.png' && $file_type != '.gif' && $file_type != 'jpeg'){
              $errors['img_name'] = 'type';
            }
      }
      else {
            //ファイルがない時の処理
            $errors['img_name'] = 'blank';
      }
    //   echo "<pre>";
    //   var_dump($_FILES);
    //   echo "</pre>";
      if (empty($errors)) {
        //エラーがなかった時の処理
            date_default_timezone_set('Asia/Manila');
            $date_str = date('YmdHis');
            $submit_file_name = $date_str.$file_name;
            echo $submit_file_name;
            //$_FILESはinput typeを選んだ時に選択されるスーパーグローバル変数のこと。その後のinput_img_nameはinput typeのnameと連動をされてる。
            move_uploaded_file($_FILES['input_img_name']['tmp_name'], '../user_profile_img/'.$submit_file_name);

              $_SESSION['register']['name'] = $_POST['input_name'];
              $_SESSION['register']['email'] = $_POST['input_email'];
              $_SESSION['register']['password'] = $_POST['input_password'];
              // 上記3つは$_SESSION['register'] = $_POST;という書き方で1文にまとめることもできます
              $_SESSION['register']['img_name'] = $submit_file_name;


            header('Location: check.php');
            exit();
      }
  }
}

  

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="../assets/font-awesome/css/font-awesome.css">
   <link rel="stylesheet" type="text/css" href="../assets/css/style.css">

</head>
<body style="margin-top: 60px">
  <div class="container">
    <div class="row">
      <!-- ここにコンテンツ -->
       <div class="col-xs-8 col-xs-offset-2 thumbnail">
        <h2 class="text-center content_header">アカウント作成</h2>
        <form method="POST" action="signup.php" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">ユーザー名</label>
            <input type="text" name="input_name" class="form-control" id="name" placeholder="山田 太郎">
              <?php if(isset($errors['name']) && $errors['name'] == 'blank') { ?>
              <p class="text-danger">ユーザー名を入力してください</p>
            <?php } ?>
             <?php if(isset($errors['name']) && $errors['name'] == 'length') { ?>
              <p class="text-danger">ユーザー名は4~16文字の間で入力してください。</p>
            <?php } ?>
          </div>
          <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="input_email" class="form-control" id="email" placeholder="example@gmail.com">
            <?php if(isset($errors['email']) && $errors['email'] == 'blank'){ ?>
            <p class="text-danger">emailを入力してください</p>
            <?php } ?>
            <?php if(isset($errors['email']) && $errors['email'] == 'duplicate'){ ?>
            <p class="text-danger">すでに登録されているメールアドレスです。</p>
            <?php } ?>
          </div>
          <div class="form-group">
            <label for="password">パスワード</label>
            <!--  type=passwordと押すとパスワードが〇〇表示になる  -->
            <input type="password" name="input_password" class="form-control" id="password" placeholder="4 ~ 16文字のパスワード">
             <?php if(isset($errors['password']) && $errors['password'] == 'blank'){ ?>
            <p class="text-danger">パスワードを入力してください</p>
            <?php } ?>
             <?php if(isset($errors['password']) && $errors['password'] == 'length'){ ?>
            <p class="text-danger">パスワードは4~16文字で入力をしてください。</p>
            <?php } ?>
          </div>
          <div class="form-group">
            <label for="img_name">プロフィール画像</label>
            <input type="file" name="input_img_name" id="img_name" accept="image/*">
            <?php if(isset($errors['img_name']) && $errors['img_name'] == 'blank'){ ?>
            <p class="text-danger">プロフィール画像を選択してください。</p>
            <?php } ?>
            <?php if(isset($errors['img_name']) && $errors['img_name'] == 'type'){ ?>
            <p class="text-danger">拡張子が「png」「jpg」「gif」の画像を選択してください</p>
            <?php } ?>
          </div>
          <input type="submit" class="btn btn-default" value="確認">
          <a href="../signin.php" style="float: right; padding-top: 6px;" class="text-success">サインイン</a>
        </form>
      </div>

    </div>
  </div>
  <script src="../assets/js/jquery-3.1.1.js"></script>
  <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="../assets/js/bootstrap.js"></script>
</body>
</html>
