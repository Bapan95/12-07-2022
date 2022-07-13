<?php 
require_once("../lib/config.php");
$logged_user_id = my_session('user_id');
if(isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')){
$logged_user_id = $_REQUEST['user_id'];
}
$role_id =  my_session('role_id');
$action_type = $_REQUEST['action_type'];
$return_data  = array();
$booking_id = $_REQUEST['booking_id'];
$invoice_id = $_REQUEST['invoice_id'];
$name = $_REQUEST['name'];
if($action_type=="INVOICE_LISTING")
{
 $query = "SELECT im.invoice_id,im.invoice_no,ifnull(date_format(im.eff_start_date,'%d-%m-%Y'),'') eff_start_date,ifnull(date_format(im.eff_end_date,'%d-%m-%Y'),'') eff_end_date FROM invoice_master im WHERE im.booking_id='".$booking_id."'";
  $result = $db->query($query);
  if($result){
  while($data=mysqli_fetch_assoc($result))
  {
  	$ret[] = $data;
  }
	}
	$booking_header_query = "SELECT date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.billing_to,'%d-%m-%Y') billing_to FROM booking_header bh WHERE bh.booking_id='".$booking_id."'";
  $booking_header_result = $db->query($booking_header_query);
  if($booking_header_result){
  $booking_header_data=mysqli_fetch_assoc($booking_header_result);
  }
$return_data  = array('status' => true, 'invoice_list'=>$ret,'booking_header_data'=>$booking_header_data);
echo json_encode($return_data);
}
elseif($action_type=="ADD_EDIT_INVOICE")
{
	$eff_end_date=$_REQUEST['eff_end_date'];
	$eff_end_date =date('Y-m-d', strtotime($eff_end_date));
	$eff_start_date=$_REQUEST['eff_start_date'];
	$eff_start_date =date('Y-m-d', strtotime($eff_start_date));
	$chk_code = "SELECT COUNT(1) AS cnt FROM invoice_master WHERE booking_id = '$booking_id' AND 
	(eff_start_date BETWEEN '$eff_start_date' AND '$eff_end_date' OR eff_end_date BETWEEN '$eff_start_date' AND '$eff_end_date');";
	  $chk_res = $db->query($chk_code);
	 $chk_detail = mysqli_fetch_assoc($chk_res);
   $chk_detail['cnt'];
  if($chk_detail['cnt']>0 && !$invoice_id)
  {$id = 0;
  }  
elseif(!$invoice_id)
{	
 $query =  "INSERT INTO invoice_master(booking_id,invoice_no,eff_start_date,eff_end_date,created_by,created_ts)VALUES
  ('".$booking_id."','".$name."','".$eff_start_date."','".$eff_end_date."','".$logged_user_id."',now())";
  $result = $db->query($query);
  
  $chk_invoice = "SELECT COUNT(1) AS cnt FROM invoice_master WHERE booking_id = '$booking_id';";
	  $chk_invoice_res = $db->query($chk_invoice);
	 $chk_invoice_detail = mysqli_fetch_assoc($chk_invoice_res);
   $cnt=$chk_invoice_detail['cnt'];
   if($cnt==0){
	 $update_query="UPDATE notification_master SET view_status=1,action_taken=1 WHERE ref_id = '$booking_id' AND ref_type='Invoice'"; 
	 $update_res = $db->query($update_query); 
   }
  if($result)
  {
	 $msg = "Insert Successfully !";
  	$id = 1;
  }
  else
  {
  	$msg = "Try again !";
  }
}
else
{
   $upd_query = "UPDATE invoice_master SET invoice_no='".$name."',eff_start_date='".$eff_start_date."',eff_end_date='".$eff_end_date."',updated_by='".$logged_user_id."' WHERE invoice_id='".$invoice_id."'";
   $res = $db->query($upd_query);
   if($res)
   {
   	//$msg = "Updated sucessfully !";
   $id = 2;
   }
   else
   {
   	$msg = "Try again !";
   }
   

}
$return_data  = array('status' => true,'msg'=>$msg,'id'=>$id);
 echo json_encode($return_data);
}elseif ($action_type == "SELECT_INVOICE") {
	$query = "SELECT im.invoice_id,im.invoice_no,ifnull(date_format(im.eff_start_date,'%d-%m-%Y'),'') eff_start_date,ifnull(date_format(im.eff_end_date,'%d-%m-%Y'),'') eff_end_date FROM invoice_master im WHERE im.booking_id='".$booking_id."'";
		$result = $db->query($query);
        $row = mysqli_fetch_assoc($result);
		$return_data = array('status' => true,'invoice_value' => $row);
        echo json_encode($return_data);		
	}	




?>