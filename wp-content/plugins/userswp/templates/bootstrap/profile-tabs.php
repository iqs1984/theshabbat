<?php
/**
 * Profile tabs template (default)
 *
 * @ver 0.0.1
 */

if(isset($_GET['category']))
{
	if($_GET['category'] == 'Artists-Performers'){
		$get_category = 'Artists | Performers';
	}elseif($_GET['category'] == 'Shadchanim-Singles'){
		$get_category = 'Shadchanim | Singles';
	}else{
		$get_category = $_GET['category'];
	}
}
$css_class = ! empty( $args['css_class'] ) ? esc_attr( $args['css_class'] ) : 'border-0';
$output = ! empty( $args['output'] ) ? esc_attr( $args['output'] ) : '';
$account_page = uwp_get_page_id('account_page', false);
$tabs_array = $args['tabs_array'];
$active_tab = $args['active_tab'];
$greedy_menu_class = empty($args['disable_greedy']) ? 'greedy' : '';
$banner_url   = $args['banner_url'];
do_action( 'uwp_template_before', 'profile-tabs' );
$user = uwp_get_displayed_user();

if(!$user){
	return;
}

if($output === '' || $output=='head'){

$userprofilemeta = uwp_get_usermeta_row($user->ID); 
$cat_list = explode(',' , $userprofilemeta->choose_multiple_category);
$cat_arr = ucfirst($get_category);
for($i = 0; $i <= count($cat_list); $i++)
{

	if($cat_list[$i] == $_GET['category']){
		continue;
	}else{

		$cat_arr .= ', '.ucfirst($cat_list[$i]);
	}
	
}


?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css">

<div class="container">
 	<div class="row">
  		<div class="col-lg-12 col-md-12">
			<div class="ban-sec">
				<!--<img width="100%" alt="img" src="<?php //echo ($userprofilemeta->image_1)? $userprofilemeta->image_1 :'https://www.theshabbat.org/wp-content/uploads/linkdin/background.jpg';?>" /> -->
				<style>
				.pro-banner {
					background-image: url(<?php echo ($userprofilemeta->image_1)? $userprofilemeta->image_1 :'https://www.theshabbat.org/wp-content/uploads/linkdin/background.jpg';?>);
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
				
				@media (min-width:320px) and (max-width:767px) { 
				 .pro-banner.dd {
    padding-bottom: 200px;
}
				}
				@media (min-width:768px) and (max-width:991px) { 
				.pro-banner.dd {
    padding-bottom: 80px;
}
}
				</style>
				<div class="pro-banner dd">
				 <div class="container">
				  <div class="row">
				   <div class="col-lg-6 col-md-5"></div>
				   <div class="col-lg-3 col-md-1"></div>
				   <div class="col-lg-3 col-md-6">
					<div class="pro-txt">
					 <p><span>Who’s in PROFILES?</span> Directory of independent people that do not belong to our organization but may be available to attend your event.</p>
					 <p><span>BUILD YOUR EVENT</span> Invite rabbis, speakers, entertainers, and staff to your event with just a click. Compensation is negotiated between the parties.</p>
					</div>
				   </div>
				  </div>
				 </div>
				</div>
				<div class="ban-pic"><h1><img class="ban-img" width="100%" alt="img" src="<?php echo ($userprofilemeta->avatar_thumb)?get_avatar_url($user->ID,'500'):esc_url($userprofilemeta->upload_a_pic);?>"><div><span><?php echo $userprofilemeta->first_name;?></span><?php echo rtrim($cat_arr, ', '); ?> 
				 <ul>
                  <li><i class="fa fa-calendar"></i> Book</li>
				  <li><i class="fa fa-plus"></i> Invite</li>
				  <li><i class="fa fa-comment"></i> Contact</li>
                 </ul>
				</div></h1></div>
			</div>
		</div>
 	</div>
</div><!-- container end here -->
<nav class="navbar navbar-expand-xl navbar-light bg-white  mb-4 p-xl-0 <?php echo esc_attr($greedy_menu_class); ?> hide">
	<div class="w-100 justify-content-center p-xl-0 border-bottom">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#uwp-profile-tabs-nav" aria-controls="navbarNavDropdown-1" aria-expanded="false" aria-label="Toggle navigation" style=""><span class="navbar-toggler-icon"></span></button>
		<div class="collapse navbar-collapse" id="uwp-profile-tabs-nav">
			<ul class="navbar-nav flex-wrap m-0 list-unstyled">
				<?php
				if(!empty($tabs_array)) {
					foreach ($tabs_array as $tab) {
						$tab_id = $tab['tab_key'];
						$tab_url = uwp_build_profile_tab_url($user->ID, $tab_id, false);

						$active = $active_tab == $tab_id ? ' active border-bottom border-primary border-width-2' : '';

						if ($active_tab == $tab_id) {
							$active_tab_content = $tab['tab_content_rendered'];
						}

						$append_hash = apply_filters('uwp_add_tab_content_hashtag', true, $tab, $user);
						$tab_url = $append_hash ? esc_url($tab_url).'#tab-content' : esc_url($tab_url);

						?>
						<li id="uwp-profile-<?php echo esc_attr( $tab_id ); ?>"
						    class="nav-item <?php echo $active; ?> list-unstyled m-0">
								<?php
                                $content = '<span class="uwp-profile-tab-label uwp-profile-'.esc_attr( $tab_id ).'-label">'.esc_html__($tab['tab_name'], 'userswp').'</span>';
                                echo aui()->button(array(
									'type'       =>  'a',
									'href'       => $tab_url,
									'class'      => 'nav-link',
									'icon'       => esc_attr($tab['tab_icon']),
									'content'    => $content,
								));
								?>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
	</div>
</nav>
<?php
}

if ( $output === '' || $output == 'body' ) {
	if ( isset( $active_tab_content ) && ! empty( $active_tab_content ) ) {
		?>
        <!--<div id="tab-content" class="uwp-profile-content">
            <div class="uwp-profile-entries">
				<?php

				//echo $active_tab_content;
				?>
            </div>
        </div>-->
	
<?php

?>	


<div class="outer">
 <div class="container">
  <div class="row">
   <div class="col-lg-8 col-md-7">
    <h6><a href="https://www.theshabbat.org/profiles">Profiles</a> / <a href='https://www.theshabbat.org/profiles?view_all=<?php echo urlencode($_GET['category']); ?>'><?php echo ucfirst($get_category) ?></a> / <?php echo ucwords($userprofilemeta->first_name);?></h6>
    <pre><?php echo $userprofilemeta->about_me;?></pre>
    
   </div><!-- col end here -->
   
   <div class="col-lg-4 col-md-5">
    <h2>Videos Of <?php echo $userprofilemeta->first_name;?></h2>
	<?php if(@unserialize($userprofilemeta->youtube)>0){ ?>
    <ul>
	<?php foreach(@unserialize($userprofilemeta->youtube) as $video)
	{ 
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $match);
		$video_id = $match[1];
		$thumbnail="http://img.youtube.com/vi/".$video_id."/maxresdefault.jpg";
	?>

	 <li><a href="<?php echo $video;?>" target="_blank"><img src="<?php echo $thumbnail ?>"><?php echo substr(explode(' - YouTube',explode('</title>',explode('<title>',file_get_contents("https://www.youtube.com/watch?v=$video_id"))[1])[0])[0],0,50); ?>...</a></li>
	<?php } ?>
	</ul><!-- ul end here -->
	<?php } ?>
   </div><!-- col end here -->
  </div><!-- row end here -->
 </div><!-- container end here -->
</div><!-- outer end here -->


<div class="similar-sec">
 <div class="container">
  <div class="row">
   <div class="col-lg-12 col-md-12"><h2>Other Profiles in <?php echo $get_category ?> Category</h2>
    <div id="similar-sec" class="owl-carousel">
	<?php $users = get_uwp_users_list(); 
		$listusers=$users['users'];
		shuffle($listusers);
        foreach ($listusers as $uwp_user)
		{ 
			$userprofilemeta = uwp_get_usermeta_row($uwp_user->ID);
			$multiple_category = explode(',' , $userprofilemeta->choose_multiple_category);
			if(in_array($get_category, $multiple_category)){ ?>
			<div class="simi-box">
				<a href="<?php echo get_author_posts_url($uwp_user->ID).'?category='.urlencode($get_category); ?>"><img width="100%" alt="img" src="<?php echo ($userprofilemeta->avatar_thumb)?get_avatar_url($uwp_user->ID,'500'):esc_url($userprofilemeta->upload_a_pic);?>" />
				</a><p><?php echo ucfirst($userprofilemeta->first_name) ?></p>
			</div><!-- simi-box end here -->
		<?php } } ?>
    </div><!-- similar-sec end here -->  
   </div><!-- col end here -->
  </div><!-- row end here -->  
 </div><!-- container end here -->  
</div><!-- similar-sec end here -->
<?php } } ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script> 
jQuery(document).ready(function(){   
 jQuery("#similar-sec").owlCarousel({
  items:6,
  responsive: {
  1200: { items:6 },
  992: { items:4 },
  768: { items:3 },
  459: { items:2 },
  320: { items:1 }
  },
  loop: false,
  slideBy: 6,
  nav: true,
  dots: false,
  autoplay: false
 })
});
</script>