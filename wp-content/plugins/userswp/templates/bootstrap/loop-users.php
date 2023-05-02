<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if($_GET['msg'])
{
  if($_GET['msg'] == 'success')
  {
    $success_msg = $_GET['msg'];
  }else{
    $error_msg = $_GET['msg'];
  }
}

global $uwp_in_user_loop;
global $wpdb;
$uwp_in_user_loop = true;
$the_query = isset($args['template_args']['the_query']) ? $args['template_args']['the_query'] : '';
$maximum_pages = isset($args['template_args']['maximum_pages']) ? $args['template_args']['maximum_pages'] : '';
$users = isset($args['template_args']['users']) ? $args['template_args']['users'] : '';
$total_users = isset($args['template_args']['total_users']) ? $args['template_args']['total_users'] : '';

$multiple_cat_option = $wpdb->get_var("SELECT option_values FROM `wpip_uwp_form_fields` WHERE field_type_key = 'multiselect'");
$cat_string = preg_replace("/,([\s])+/", ",", $multiple_cat_option);
$category_arrays = explode(",", $cat_string);
$selected_array=$_GET['category'];

if(isset($_POST['PorfileSubmit']))
{ 
  $name = $_POST['profile-name'];
  $email = $_POST['user-email'];
  $category_list = implode(",",$_POST['category']);
  $category_string = preg_replace("/,([\s])+/", ",", $category_list);
  $about = $_POST['user_about'];
  $yt_video_list = serialize($_POST['yt-video']);
  $user_ip = $_SERVER['REMOTE_ADDR'];
  
  $filename1 = $_FILES["profile_img"]["name"];
  $tempname1 = $_FILES["profile_img"]["tmp_name"];
  $folder1 = "wp-content/uploads/2023/".$filename1;
  $profile_url ="https://www.theShabbat.org/".$folder1;

  $filename2 = $_FILES["banner_img"]["name"];
  $tempname2 = $_FILES["banner_img"]["tmp_name"];
  $folder2 = "wp-content/uploads/2023/".$filename2;
  $banner_url ="https://www.theShabbat.org/".$folder2;

  $randrom_password = 'theShabbat#123';

  $userData = array('user_login' => $name, 'user_pass' => $randrom_password, 'user_nicename' => $name, 'user_email' => $email, 'display_name' => $name);

  $user_data_id = wp_insert_user( $userData );

  $success_sql = $wpdb->insert('wpip_uwp_usermeta',array('user_id' => $user_data_id ,'user_ip' => $user_ip, 'email' => $email, 'first_name' => $name, 'youtube' => $yt_video_list, 'upload_a_pic' => $profile_url, 'image_1' => $banner_url, 'choose_multiple_category' => $category_string, 'about_me' => $about));

  if($success_sql){
    move_uploaded_file($tempname1, $folder1);
    move_uploaded_file($tempname2, $folder2);
    $login_url = 'https://www.theshabbat.org/login';
    $to = $email; 
    $from = 'info@theshabbat.org'; 
    $subject = "Your Profile is Live at The Shabbat"; 
    
    $headers = "MIME-Version: 1.0" . "\r\n"; 
    $headers .= "Content-type:text/html" . "\r\n"; 
    
    $headers .= 'From: <'.$from.'>' . "\r\n"; 
    $content='
    <html>
    <body>
    <h3>Shabbat Shalom!</h3>
    <p>The Worlds first year-round SHABBAT RESORT on the Las Vegas strip launches this Pesach. Enjoy 5-star kosher cuisine, luxury accommodations, Shabbat elevators, non-automatic bathroom fixtures and event separate swimming,</p>
    <p>One of our guests created your profile. <a href="'.get_author_posts_url($user_data_id).'?category='.$_POST['category'][0].'">Click Here to view your profile.</a></p>
    <p>You can login with the following access details:</p>
    <p>Login URL : <a href="'.$login_url.'">'.$login_url.'</p>
    <p>User Name : '.$email.'</p>
    <p>Temporary Password : '.$randrom_password.'</p>
    <p>Why have a Profile?</p>
    <p><strong>1) Discounts on shomer-Shabbos accommodations on the Las Vegas strip,</strong></p>
    <p><strong>2) Donation per guest referral,</strong></p>
    <p><strong>3) Eligibility to be a Paid guest speaker in "Build Your Own Event" section.</strong></p>
    <p>Sincerely,</p>
    <p>The Shabbat Inc</p>
    </body>
    </html>
    ';
    UsersWP_Mails::uwp_mail($to, $subject, $content,$headers,'');
    header('Location: '.home_url().'/profiles?msg=success');

  }else{

    header('Location: '.home_url().'/profiles?msg=error');
  }

}  

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css">
<style>
.pro-banner {
    background-image: url(https://www.theshabbat.org/wp-content/uploads/2023/03/pbanner.jpg);
    background-position: top center;
    background-size: cover;
    padding: 40px 0;
}
.pro-banner .col-lg-6 {
    align-self: center;
}
.pro-banner .pro-txt {
    background-color: #fff;
    border: 2px solid #000;
    padding: 20px;
}
.pro-banner .pro-txt p {
    color: #000;
    font-size: 15px;
    margin: 0 0 12px;
}
.pro-banner .pro-txt p span {
    display: block;
    font-size: 18px;
    font-weight: 900;
    text-transform: uppercase;
}
</style>

<!--<img alt="pro-banner" class="pro-banner-1" width="100%" src="https://www.theShabbat.org/wp-content/uploads/2023/03/pro_banner_new.jpg" />-->
<div class="pro-banner">
 <div class="container">
  <div class="row">
   <div class="col-lg-6 col-md-7"><img width="100%" src="https://www.theshabbat.org/wp-content/uploads/2023/03/Group-4.png" /></div>
   <div class="col-lg-3 col-md-1"></div>
   <div class="col-lg-3 col-md-4">
    <div class="pro-txt">
	 <p><span>Who’s in PROFILES?</span> Directory of independent people that do not belong to our organization but may be available to attend your event.</p>
     <p><span>BUILD YOUR EVENT</span> Invite rabbis, speakers, entertainers, and staff to your event with just a click. Compensation is negotiated between the parties.</p>
	</div>
   </div>
  </div>
 </div>
</div>
<div class="uwp-users-loop">

  <div class="sr-sec">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 col-md-8">
          <div class="sr-box">
            <form method="get">
              <input type="text" name="uwps" value="<?php echo @$_GET['uwps']; ?>" placeholder="Search by Name">
              <select class="multiple-search" name="category[]" multiple="multiple">
                <?php
                  foreach ($category_arrays as $option) { ?>
                <option value="<?php echo $option ?>" <?php echo (in_array($option,$selected_array))?'selected':''; ?>>
                  <?php echo $option ?>
                </option>
                <?php } ?>
              </select>
              <button type="submit" class="sr-search"><i class="fa fa-search"></i></button>
            </form>
          </div>
        </div><!-- col end here -->
        <div class="col-lg-6 col-md-4"><button id="add-profile-btn" type="button">Add Profile</button></div>
      </div><!-- row end here -->
    </div><!-- container end here -->
  </div><!-- sr-sec end here -->

  <div class="guest-sec">
    <div class="container">
     <div class="row">
      <div class="col-lg-12 col-md-12">
       <h2>INVITE GUESTS & STAFF FOR YOUR EVENT</h2>

       </div>
       </div>
       </div>
      </div>

  <div class="modal add-modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
          <form action="" method="post" enctype="multipart/form-data">
            <h2>Create Profile</h2>
            <input class="box" type="text" name="profile-name" placeholder="Profile Name" required>
            <input class="box" type="email" name="user-email" placeholder="Email" required>
            <!-- <input class="box" type="text" name="password" placeholder="Temporary Password" > -->
            <h3>Upload Profile Image</h3>
            <input class="box" type="file" name="profile_img" required>
            <h3>Upload Banner</h3>
            <input class="box" type="file" name="banner_img" required>
            <h3>Choose Categories</h3>
            <select class="multiple-category" name="category[]" multiple="multiple" required>
              <?php
                    foreach ($category_arrays as $option) { ?>
              <option value="<?php echo $option ?>">
                <?php echo $option ?>
              </option>
              <?php } ?>
            </select>
            <h3>About</h3>
            <textarea class="box" name="user_about" id="quick_about"></textarea>
            <h3>YouTube Video Links</h3>
            <div class="yt-div">
              <div class="yt-box">
                <input class="box" type="url" name="yt-video[]" id="yt" placeholder="YouTube Url" title="YouTube Url">
                <button class="add-yt-field">+</button>
              </div>
            </div>
            <button type="submit" name="PorfileSubmit">Create Profile</button>
          </form>
        </div><!-- modal-body end here -->
      </div><!-- modal-content end here -->
    </div><!-- modal-dialog end here -->
  </div><!-- modal end here -->

  <div class="uwp-users-loop">
    <?php
    if ($users) { ?>
    <div class="outer dd">
      <div class="container">
        <?php
          global $uwp_user;
          
          $original_user = $uwp_user;
          if (!$_GET['view_all'] and !$_GET['category']) 
          { 
            foreach ($category_arrays as $category_array) { $i = 1; $j=0;
              ?>
        <div class="row only_mar">
          <?php 
                $k = 0;
                foreach ($users as $uwp_user) {
                  $usermetadata = uwp_get_usermeta_row($uwp_user->ID);
                  $multiple_category_other = explode(',', $usermetadata->choose_multiple_category);
                  if (in_array($category_array, $multiple_category_other)) {
                    
                    if ($i == 1) {
                      if ($category_array == 'Artists-Performers') {
                        $cat_name = 'Artists | Performers';
                      } elseif ($category_array == 'Shadchanim-Singles') {
                        $cat_name = 'Shadchanim | Singles';
                      } else {
                        $cat_name = $category_array;
                      }
                      ?>
          <div class="col-lg-12 col-md-12">
            <h2><a href="<?php echo 'https://www.theShabbat.org/profiles?view_all=' . urlencode($category_array); ?>">
                <?php echo $cat_name."<span id=".strtok($cat_name," ")."></span>"; ?>
              </a><a
                href="<?php echo 'https://www.theShabbat.org/profiles?view_all=' . urlencode($category_array); ?>">View
                All</a></h2>
          </div>
          <div class="similar-sec owl-carousel">
            <?php $j=1; } $i++; ?>

            <div class="simi-box">
              <a href="<?php echo get_author_posts_url($uwp_user->ID) . '?category=' . urlencode($category_array); ?>"><img
                  alt="img4" width="100%"
                  src="<?php echo ($usermetadata->avatar_thumb) ? get_avatar_url($uwp_user->ID, '500') : esc_url($usermetadata->upload_a_pic); ?>" /></a>
              <p><a
                  href="<?php echo get_author_posts_url($uwp_user->ID) . '?category=' . urlencode($category_array); ?>">
                  <?php echo $usermetadata->first_name; ?>
                </a>
              </p>
            </div>
            <?php $k++; } 
                } if ($j) { ?>

          </div>
          <?php }  ?>
        </div><!-- row end here -->
        <script>
          var count = '<?php echo $k; ?>';
          var span_id = '<?php echo strtok($cat_name," "); ?>';
          if(count > 0){
            $('#' + span_id).text(' (' + count + ')');
          }
        </script>
        <?php  } 
          } else if ($_GET['view_all'] and !$_GET['SubmitButton']) {

            if ($_GET['view_all'] == 'Artists-Performers') {
              $cat_profile_name = 'Artists | Performers';
            } elseif ($_GET['view_all'] == 'Shadchanim-Singles') {
              $cat_profile_name = 'Shadchanim | Singles';
            } else {
              $cat_profile_name = $_GET['view_all'];
            }

            ?>

        <div class="row">
          <div class="col-lg-8 col-md-7">
            <h6><a href="https://www.theShabbat.org/profiles">Profiles</a> /
              <?php echo ucfirst($cat_profile_name); ?>
            </h6>
            <pre><?php echo $userprofilemeta->about_me; ?></pre>
          </div><!-- col end here -->
          <div class="col-lg-4 col-md-5"></div>
        </div><!-- col end here -->


        <?php foreach ($category_arrays as $category_array) {
              if ($category_array == $_GET['view_all']) { ?>
        <div class="row">
          <?php $i = 1;
                  foreach ($users as $uwp_user) {
                    $usermetadata = uwp_get_usermeta_row($uwp_user->ID);
                    $multiple_category = explode(',', $usermetadata->choose_multiple_category);
                    if (in_array($category_array, $multiple_category) and $category_array == $_GET['view_all']) {
                      if ($i == 1) {
                        if ($category_array == 'Artists-Performers') {
                          $cat_name = 'Artists | Performers';
                        } elseif ($category_array == 'Shadchanim-Singles') {
                          $cat_name = 'Shadchanim | Singles';
                        } else {
                          $cat_name = $category_array;
                        }
                        ?>
          <div class="col-lg-12 col-md-12">
            <h2>
              <?php echo $cat_name; ?>
            </h2>
          </div>
          <?php }
                      $i++; ?>
          <div class="col-lg-3 col-md-4">
            <div class="pro-box">
              <a href="<?php echo get_author_posts_url($uwp_user->ID) . '?category=' . urlencode($category_array); ?>"><img
                  alt="img4" width="100%"
                  src="<?php echo ($usermetadata->avatar_thumb) ? get_avatar_url($uwp_user->ID, '500') : esc_url($usermetadata->upload_a_pic); ?>" /></a>
              <h6><a
                  href="<?php echo get_author_posts_url($uwp_user->ID) . '?category=' . urlencode($category_array); ?>">
                  <?php echo $usermetadata->first_name; ?>
                </a><span>
                  <?php //echo $cat_name; ?>
                </span>
              </h6>
            </div>
          </div>
          <?php }
                  } ?>
        </div><!-- row end here -->

        <?php }
            }
          } else if ($_GET['category'] != '') {
            $search_cat_arr = $_GET['category'];
            $flag = false;
            ?>
        <?php foreach ($search_cat_arr as $category_arr) { $i = 1; $j=0; ?>
        <div class="row only_mar">
          <?php
                $c = 0;
                foreach ($users as $uwp_user) {

                  $usermetadata = uwp_get_usermeta_row($uwp_user->ID);
                  $multiple_category = explode(',', $usermetadata->choose_multiple_category);
                  if (in_array($category_arr, $multiple_category)) {
                    if ($i == 1) {
                      if ($category_arr == 'Artists-Performers') {
                        $cat_name = 'Artists | Performers';
                      } elseif ($category_arr == 'Shadchanim-Singles') {
                        $cat_name = 'Shadchanim | Singles';
                      } else {
                        $cat_name = $category_arr;
                      }
                      ?>
          <div class="col-lg-12 col-md-12">
            <h2>
              <?php echo $cat_name."<span id=".strtok($cat_name," ")."></span>"; ?>
            </h2>
          </div>
          <div class="similar-sec owl-carousel">
            <?php $j=1; }
                    $i++; ?>

            <div class="simi-box">
              <a href="<?php echo get_author_posts_url($uwp_user->ID) . '?category=' . urlencode($category_arr); ?>"><img
                  alt="img4" width="100%"
                  src="<?php echo ($usermetadata->avatar_thumb) ? get_avatar_url($uwp_user->ID, '500') : esc_url($usermetadata->upload_a_pic); ?>" /></a>
              <p><a href="<?php echo get_author_posts_url($uwp_user->ID) . '?category=' . urlencode($category_arr); ?>">
                  <?php echo $usermetadata->first_name; ?>
                </a><span>
                  <?php //echo $cat_name; ?>
                </span>
              </p>
            </div>

            <?php $c++; 
                  $flag = true; }
                } if ($j) { ?>
          </div>
          <?php } ?>
        </div>
        <script>
          var count = '<?php echo $c; ?>';
          console.log(count);
          var span_id = '<?php echo strtok($cat_name," "); ?>';
          if(count > 0){
            $('#' + span_id).text(' (' + count + ')');
          }
        </script>

        <?php  }
          if($flag == false){
            echo "No Result Found";
          } }  ?>
      </div><!-- container end here -->
    </div><!-- outer end here -->

    <?php $uwp_user = $original_user; ?>
    <?php
      /* Restore original Post Data */
      wp_reset_postdata();
    } else {
      // no users found
      uwp_no_users_found();
    }
    do_action('uwp_after_users_list');
    ?>
  </div><!-- .uwp-users-loop -->

  <?php
  $uwp_in_user_loop = false;
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script>
    jQuery(document).ready(function () {
      jQuery(".similar-sec").owlCarousel({
        items: 6,
        responsive: {
          1200: { items: 6 },
          992: { items: 4 },
          768: { items: 3 },
          459: { items: 2 },
          320: { items: 1 }
        },
        loop: false,
        slideBy: 6,
        nav: true,
        dots: false,
        autoplay: false
      })
    });
  </script>


  <script>

    var success_msg = '<?php echo $success_msg ?>';
    var error_msg = '<?php echo $error_msg ?>';

    jQuery(document).ready(function () {
      jQuery('.multiple-search').select2({
        placeholder: "Search by Category"
      });

    });

    jQuery(document).ready(function () {
      jQuery('.multiple-category').select2();
    });


    jQuery(function () {
      jQuery('#add-profile-btn').on('click', function () {
        if (document.body.classList.contains('logged-in')) {
          jQuery('#myModal').modal('show');
        } else {
          window.location.href = 'https://www.theShabbat.org/login';
        }
      });
    });

    jQuery(document).ready(function () {
      var wrapper = jQuery(".yt-div");
      var add_button = jQuery(".add-yt-field");
      jQuery(add_button).click(function (e) {
        e.preventDefault();
        jQuery(wrapper).append('<div class="yt-box"><input class="box" type="url" name="yt-video[]" id="yt" placeholder="YouTube Url" title="YouTube Url"><a href="#" class="remove-yt-field">-</a></div>');
      });
      jQuery(wrapper).on("click", ".remove-yt-field", function (e) {
        e.preventDefault(); jQuery(this).parent('div').remove(); x--;
      })
    });

    jQuery(document).ready(function () {
      if (success_msg != '') {
        Swal.fire({
          icon: 'success',
          title: 'Profile Created Successfully',

        }).then(function () {
          window.location.href = "https://www.theshabbat.org/profiles";
        })
      }
    });

    jQuery(document).ready(function () {
      if (error_msg != '') {
        Swal.fire({
          icon: 'error',
          title: 'Something went wrong!',
        }).then(function () {
          window.location.href = "https://www.theshabbat.org/profiles";
        })
      }
    });

  </script>