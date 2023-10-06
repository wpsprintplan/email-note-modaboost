<?php
//Database connection details
// $servername     = DB_HOST;
// $username       = DB_USER;
// $password       = DB_PASSWORD;
// $dbname         = DB_NAME;

// // Create a connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check the connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }




// // $request = $_POST['request'];

// // // Datatable data
// // if($request == 1){
// //    ## Read value
// //    $draw = $_POST['draw'];
// //    $row = $_POST['start'];
// //    $rowperpage = $_POST['length']; // Rows display per page
// //    $columnIndex = $_POST['order'][0]['column']; // Column index
// //    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
// //    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
// //    $searchValue = mysqli_real_escape_string($con,$_POST['search']['value']); // Search value

// //    ## Search 
// //    $searchQuery = " ";
// //    if($searchValue != ''){
// //       $searchQuery .= " and (notes like '%".$searchValue."%' or 
// // email like '%".$searchValue."%') ";
// //    }

// //    ## Total number of records without filtering
// //    $sel = mysqli_query($con,"select count(*) as allcount from ".EN_TABLE_NAME."");
// //    $records = mysqli_fetch_assoc($sel);
// //    $totalRecords = $records['allcount'];

// //    ## Total number of records with filtering
// //    $sel = mysqli_query($con,"select count(*) as allcount from ".EN_TABLE_NAME." WHERE 1 ".$searchQuery);
// //    $records = mysqli_fetch_assoc($sel);
// //    $totalRecordwithFilter = $records['allcount'];

// //    ## Fetch records
// //    $empQuery = "select * from ".EN_TABLE_NAME." WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
// //    $empRecords = mysqli_query($con, $empQuery);
// //    $data = array();

// //    while ($row = mysqli_fetch_assoc($empRecords)) {
// //       $data[] = array(
// //          "email"=>$row['email'],
// //          "notes"=>$row['notes'],
// //          "status"=>$row['status'],
// //          "action"=>"<input type='checkbox' class='delete_check' id='delcheck_".$row['id']."' onclick='checkcheckbox();' value='".$row['id']."'>"
// //       );
// //    }

// //    ## Response
// //    $response = array(
// //       "draw" => intval($draw),
// //       "iTotalRecords" => $totalRecords,
// //       "iTotalDisplayRecords" => $totalRecordwithFilter,
// //       "aaData" => $data
// //    );

// //    echo json_encode($response);
// //    exit;
// // }

// // // Delete record
// // if($request == 2){

// //    $deleteids_arr = array();

// //    if(isset($_POST['deleteids_arr'])){
// //       $deleteids_arr = $_POST['deleteids_arr'];
// //    }
// //    foreach($deleteids_arr as $deleteid){
// //       mysqli_query($con,"DELETE FROM ".EN_TABLE_NAME." WHERE id=".$deleteid);
// //    }

// //    echo 1;
// //    exit;
// // }