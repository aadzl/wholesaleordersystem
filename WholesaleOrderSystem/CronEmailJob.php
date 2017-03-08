<?php

include ('mailer/class.phpmailer.php');
include ('mailer/class.smtp.php');

$db = mysql_connect('localhost', 'wholesaledbuser', 'wholesaledbuser');
if ($db) {
    mysql_select_db('wholesaleapp', $db);
}
$countS = 0;
$countF = 0;
mysql_query("SET NAMES UTF8");
$sql = mysql_query("select * from nola_email where email_status='W'", $db)or ( die(mysql_error()));

if (mysql_num_rows($sql) > 0) {
    $result = array();
    while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
        $result[] = $rlt;
    }
    foreach ($result as &$value) {
        $article = $article . "Phone Model  : <b>" . $value["pmname"] . "</b><br>";
        $email_to = $value["email_to"];
        $email_to_name = $value["email_to_name"];
        $email_body = $value["email_body"];
        $email_subject = $value["email_subject"];
        $mail = new PHPMailer(); //nesneyi oluşturuyoruz
        $mail->SetLanguage("tr", "phpmailer/language");
        $mail->CharSet = "utf-8";
        $mail->Encoding = "base64";
        $mail->IsHTML(true);
        $mail->IsSMTP(); //smtp kullanmak için
        $mail->Host = "127.0.0.1"; //mail sunucunuz
        $mail->Port = 587; //
        $mail->SMTPAuth = true; //onayı aktive ediyoruz
        $mail->Username = "order@order.com"; //kullanılacak eposta adresi
        $mail->Password = "order"; //şifre
        $mail->From = "order@order.com"; //formdan gelen mail adresi
        $mail->Fromname = "NOLACASE"; //formdan gelen isim
        $mail->AddAddress($email_to, $email_to_name);
        $mail->Subject = $email_subject; //formdan gelen konu
        $mail->Body = $email_body; //formdan gelen mesaj

        if (!$mail->Send()) {
            //return false;
            $countF++;
        } else {
            //return true;
            $query = "update nola_email set email_status='S',send_date='".date_create()->format('Y-m-d H:i:s')."' where id='" . $value["id"] . "'";
            mysql_query("SET NAMES UTF8");
            $sql = mysql_query($query, $db)or ( die(mysql_error()));
            $countS++;
        }
    }
    echo $countS.' email sent succesfully!';
    echo $countF.' email sent failed!';
}else{
    echo 'Any email found to be send!';
}
?>
