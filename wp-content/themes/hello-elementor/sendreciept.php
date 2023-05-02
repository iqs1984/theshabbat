<?php /**
    * Template Name: send reciept
    */
	require $_SERVER['DOCUMENT_ROOT'].'/dompdf/vendor/autoload.php';
	use Dompdf\Dompdf;
	
	;


	global $wpdb;
    $reciepts = $wpdb->get_results("SELECT * FROM `wpip_reciept` WHERE status =0");
	
	/* print_r($reciepts);
	die; */
	//echo $wpdb->update('wpip_reciept', array( 'status' => 2),array('status' => 1));




	
	foreach($reciepts as $reciept){
		$to = $reciept->Email; 
		$from = 'info@theshabbat.org'; 
		$subject = "Tax Receipt"; 
		
		$headers = "MIME-Version: 1.0" . "\r\n"; 
		$headers .= "Content-type:text/html" . "\r\n"; 
		
		$headers .= 'From: The Shabbat<'.$from.'>' . "\r\n"; 
		$content='
		<html>
		<head>
		<style>
            @page {
                margin: 100px 25px;
            }

            header {
                position: fixed;
                top: -60px;
                height: 50px;
                background-color: #752727;
                color: white;
                text-align: center;
                line-height: 35px;
            }

            footer {
                position: fixed;
                bottom: -40px;
                height: 50px;
                //background-color: #752727;
                //color: white;
                text-align: center;
                line-height: 35px;
				margin-left:23%;
            }
			footer p{
				 text-align: center;
			}
        </style>
		</head>
		<body>
		<center><img src="data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QMraHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjMtYzAxMSA2Ni4xNDU2NjEsIDIwMTIvMDIvMDYtMTQ6NTY6MjcgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzYgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjQwNEQ1RDhCRTNGOTExRUQ4QzE2QTdFRDE5NjUwODRFIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjQwNEQ1RDhDRTNGOTExRUQ4QzE2QTdFRDE5NjUwODRFIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDA0RDVEODlFM0Y5MTFFRDhDMTZBN0VEMTk2NTA4NEUiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NDA0RDVEOEFFM0Y5MTFFRDhDMTZBN0VEMTk2NTA4NEUiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7/7gAmQWRvYmUAZMAAAAABAwAVBAMGCg0AAAgsAAANigAAEbAAABZJ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" width="144" height="85"></center>
			<p>'.date('F d, Y').'</p>
			<h3>Dear '.$reciept->Name.',</h3>
			<p>Thank you for your generous donation!  We are grateful for your support of our Mission.The organization provided intangible religious benefits or services. Keep this receipt for your tax deduction purposes.</p>  
			<h4>Donor’s Name</h4>
			<p>'.$reciept->Name.'</p>
			
			<h4>Donation Amount</h4>
			<p> $'.number_format($reciept->TaxReceipt, 2).'</p>		

			<h4>Date Received</h4>		
			<p>'.$reciept->DatePaid.'</p>
			
			<p>On behalf of THE SHABBAT INC, may the blesser be blessed!</p>
			<p>Sincerely,</p>
			
<img src="data:image/jpeg;base64,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" width="190" height="65" />
			<p>Aryeh Avraham Rifkin</p>
			<p>President</p>
			
<p>Fed Tax ID: 92-0776737</p>
		<footer>
            <center><p>8809 LUSSO CT, LAS VEGAS, NV 89134  |  www.theshabbat.org</p></center>
        </footer>
		</body>
		</html>
		';
	$dompdf = new Dompdf();
		$dompdf->loadHtml($content); 
    
        $dompdf->render();
    $output = $dompdf->output();
    file_put_contents("reciept_document/Donation_Receipt_".str_replace(' ','_',$reciept->Name).'.pdf', $output);
       
		 //sleep(2);
		$attachments = array($_SERVER['DOCUMENT_ROOT']."/reciept_document/Donation_Receipt_".str_replace(' ','_',$reciept->Name).".pdf");
		  if(wp_mail($to, $subject,'Tax Receipt.',$headers,$attachments)){
			
			$wpdb->update('wpip_reciept', array( 'status' => 2),array('id' =>$reciept->id ));
			echo "mail sent.";
		}else{
			$wpdb->update('wpip_reciept', array( 'status' => 3),array('id' =>$reciept->id ));
			echo "mail not sent.";
		}  
	}
	
    ?>