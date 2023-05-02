<?php 


if(isset($_GET['flag']))
{
  if($_GET['flag'] == 'yes')
  {
    echo "yes";
    
  }else{

    $faq_url = 'https://www.theshabbat.org/faq/';
    $passover_url = 'www.TheShabbat.org/Passover';

    $to = 'ntnshakya@gmail.com'; 
    $from = 'info@theshabbat.org'; 
    $subject = "PASSOVER Registration"; 
    
    $headers = "MIME-Version: 1.0" . "\r\n"; 
    $headers .= "Content-type:text/html" . "\r\n"; 
    
    $headers .= 'From: <'.$from.'>' . "\r\n";

    $content1='
    <html>
    <body>
    <p>Thank you for registering for PASSOVER IN LAS VEGAS</p>
    </body>
    </html>
    ';

    $content2='
    <html>
    <body>
    <h3>Thank you for registering for PASSOVER IN LAS VEGAS - the first ever Pesach program on the Las Vegas strip!</h3>
    <p>You may still have questions...?</p>
    <p>You may find many answers online at <a href="'.$faq_url.'">'.$faq_url.'</a></p>
    <p>However, our staff is always available to answer any further questions you may have.</p>
    <p>Please feel free to reach out to me directly.</p>
    <p>Your care and comfort is our greatest concern :)</p>
    <p>Looking forward to meeting you.</p>
    <p>I did like to personally welcome you to our growing family :)</p>
    <p>Michal Taviv-Margolese</p>
    <p>michal@theshabbat.org</p>
    <p>Mobile 310.406.5437</p>
    <p>'.$passover_url.'</p>
    <p>THE SHABBAT INC, 501c3 Nonprofit in Nevada</p>
    </body>
    </html>
    ';

    $mail1 = mail($to, $subject, $content1,$headers);
    $mail2 = mail($to, $subject, $content2,$headers);

    if($mail1){
        echo "<br>mail1 sent successfully";
    }else{
        echo "mail1 not sent";
    }

    if($mail2){
        echo "<br>mail2 sent successfully";
    }else{
        echo "mail2 not sent";
    }
    
  }
}else{
    echo "no flag";
}

?>