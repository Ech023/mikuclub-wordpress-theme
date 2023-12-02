<?php

namespace mikuclub;

use mikuclub\constant\Web_Domain;

/**
 * 主页顶部热门列表 组件
 * @param int $term_id
 * @return string html内容
 */
function top_hot_posts_component($term_id)
{

	$output = '';

	//统计的键名
	$meta_key = 'views';
	//要使用的图标代码
	$icon_value = 'fa-solid fa-eye';
	//统计基础周期 是  10天;
	$range_day = 10;
	//要显示的数量
	$number = 8;


	$output = '';

	$post_list = get_hot_post_list($term_id, $meta_key, $number);



	//只有在首页的时候
	if (is_home())
	{

		//添加广告文章
		$origin_post = get_post(909069);
		$origin_post2 = get_post(914019);
		//如果文章存在
		if ($origin_post && $origin_post2)
		{

			//设置广告外链
			$adsense_post = new My_Post_Hot($origin_post);
			$adsense_post->post_title = '【BOMB】禁漫APP-YYDS 最新最全的片片都在这';
			$adsense_post->post_image = 'https://' . CDN_MIKUCLUB_FUN . '/img/bomb/thumbnail.webp';
			$adsense_post->post_href = 'https://appdown.iwaitu.xyz?adCode=6a180992-67be-4ee4-9bcf-14f806fd874b';

			$adsense_post_2 = new My_Post_Hot($origin_post2);
			if (mt_rand(0, 1))
			{
				$adsense_post_2->post_title = '【广告】初音未来正版周边';
				$adsense_post_2->post_image = 'https://' . CDN_MIKUCLUB_FUN . '/img/初音未来正版周边.webp';
				$adsense_post_2->post_href = '/shop';
			}
			else
			{
				$adsense_post_2->post_title = '【广告】动漫/游戏周边等身抱枕';
				$adsense_post_2->post_image = 'https://' . CDN_MIKUCLUB_FUN . '/img/等身抱枕.webp';
				$adsense_post_2->post_href = '/shop';
			}

			//$post_list[0] = $adsense_post;

			//添加广告文章到数组开头

			array_unshift($post_list, $adsense_post_2);
			array_unshift($post_list, $adsense_post);
			//删除最后二个元素
			array_pop($post_list);
			array_pop($post_list);
		}
	}




	//初始化排行名次
	$num            = 1;
	$post_list_html = '';

	foreach ($post_list as $my_post)
	{

		$display_class = "";
		//如果超过第4位
		if ($num > 6)
		{
			$display_class = 'd-none d-xl-flex';
		}
		else if ($num > 4)
		{
			$display_class = 'd-lg-flex';
		}

		$post_list_html .= <<<HTML
	
			<div class="col card border-0 {$display_class}">
				<div class="card-img-container position-relative ">
					<div class="position-absolute end-0 bottom-0 me-1 mb-1">
				            <div class="right-badge bg-transparent-half text-light rounded small p-1">
				                     <i class="fa-solid fa-eye"></i> {$my_post->post_views}
		                   </div>
					</div>
				    <img class="card-img-top" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
				</div>
				
				<div class="card-body py-2 overflow-hidden" style="height: 3.75rem">
					 <a class="card-link stretched-link "  title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
					 	{$my_post->post_title}
					</a>
				</div>
        	</div>

HTML;

		$num++;
	}

	//最终输出内容
	$output = <<< HTML

<div class="row row-cols-2 row-cols-lg-3 row-cols-xl-4 top-hot-post-list gy-2">
	{$post_list_html}
</div>


HTML;


	return $output;
}
