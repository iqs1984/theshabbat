<?php
/*
Template Name: event-view
*/
get_header();

if(isset($_GET['event_id']))
{
    $atts = shortcode_atts(
        [
            'id'     => 6019,
            'user'   => '',
            'fields' => '',
            'number' => '',
            'type'   => 'all', // all, unread, read, or starred.
            'sort'   => '',
            'order'  => 'asc',
        ],
        $atts
    );
    
    $entries_args = [
        'form_id' => absint( $atts[ 'id' ] ),
    ];
    
    $entries = json_decode(json_encode(wpforms()->entry->get_entries( $entries_args )), true);
}

foreach($entries as $entry) 
    {
        if($entry['entry_id'] == $_GET['event_id']){
            $fields_data = $entry['fields'];
            $fields_arr = json_decode($fields_data,true);
            $event_status = $fields_arr[19]['value'];
            if($event_status == 'Approve')
            {
                $event_name = $fields_arr[1]['value'];
                $from_date = date('F d', strtotime($fields_arr[16]['value']));
                $to_date = date('d', strtotime($fields_arr[17]['value']));
                $event_image = $fields_arr[18]['value'];
                $event_register_url = $fields_arr[22]['value'];
                $event_host_name = $fields_arr[14]['value'];
                $event_content = $fields_arr[9]['value'];

                ?>
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="ban-sec">
                                <img width="100%" alt="img" src="<?php echo $event_image ?>" style="object-fit: contain;" />
                                <div class="ban-pic"><h1><img class="ban-img" width="100%" alt="img" src="https://www.theshabbat.org/wp-content/uploads/2023/SKSI%20aryeh.png"><div><span><?php echo $event_name ?></span><?php echo $event_host_name ?>
                                <ul>
                                    <li><i class="fa fa-calendar"></i> Co-Host</li>
                                    <li><i class="fa fa-plus"></i> Invite to Attend</li>
                                    <li><i class="fa fa-comment"></i> Contact </li>
                                </ul>
                                </div></h1></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
            }
        }
    }
?>

<div class="outer evt">
 <div class="container">
  <div class="row">
   <div class="col-lg-8 col-md-7">
   <h6><a href="https://www.theshabbat.org/events">Events</a> / <?php echo str_replace('-', ' ', $_GET['event_name'])?></h6>
   <div class="evt-list"><?php echo $event_content;?></div>
   </div><!-- col end here -->
   
   <div class="col-lg-4 col-md-5">
    <h2>Other Events</h2>
    <ul>
   
	<?php
    shuffle($entries);
    $count = 0;
    foreach($entries as $entry)
	{ 
        if($count < 8 ){
            if($entry['entry_id'] == $_GET['event_id'])
            {
                continue;
            }else{
                $event_id = $entry['entry_id'];
                $fields_data = $entry['fields'];
                $fields_arr = json_decode($fields_data,true);
                $event_status = $fields_arr[19]['value'];
                if($event_status == 'Approve')
                {
                    $event_name = $fields_arr[1]['value'];
                    $from_date = date('F d', strtotime($fields_arr[16]['value']));
                    $to_date = date('d', strtotime($fields_arr[17]['value']));
                    $event_image = $fields_arr[18]['value'];
                    $event_host_name = $fields_arr[14]['value']; 
                    $event_view_url ="https://www.theshabbat.org/event-view/?event_name=".str_replace(' ', '-', $event_name)."&event_id=".$event_id;
                    ?>
                    <li>
                        <a href="<?php echo $event_view_url; ?>" target="_blank">
                            <img src="<?php echo $event_image ?>">
                       <div>
                        <h6><?php echo $event_name ?></h6>
                        <p><?php echo $event_host_name ?></p>
                        <p><?php echo $from_date.'-'.$to_date ?></p>
                       </div>
                       </a>
                    </li>
               <?php } 
            } 
        }
        $count++;
    } ?>
	</ul><!-- ul end here -->
   </div><!-- col end here -->
  </div><!-- row end here -->
  <a class="see-btn" href="<?php echo $event_register_url? $event_register_url:'#' ?>">Register</a>
 </div><!-- container end here -->
</div><!-- outer end here -->

<?php get_footer(); ?>