<?php

namespace app\admin\controller;

use think\Db;

class Statistics extends Base
{

	public function index_data()
	{
		$data['code'] = 0;
		//客户总数
		$member_count = Db::name('user')->count();
		//公用条件
		$today_start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$today_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

		//php获取上周起始时间戳和结束时间戳
		$lastweek_start = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
		$lastweek_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));

		$where_today = ['add_time' => array(array('gt', $today_start), array('lt', $today_end))];
		$where_week = ['add_time' => array(array('gt', $lastweek_start), array('lt', $lastweek_end))];

		//今日新增供应商总数
		$today_member_count = Db::name('supplier_list')->where($where_today)->count();

		//今日新增客户总数

		$today_pay_member_count = Db::name('users')->where($where_today)->count();
		//今日新增药品总数
		$today_video_released = Db::name('video')->where($where_today)->count();

		//今日评论发表总数
		$today_company_released = Db::name('comment')->where($where_today)->count();

		//今日评论点赞总数
		$today_seeker_released = Db::name('comment_like')->where($where_today)->count();

		//今日消息
		$today_message = Db::name('news')->where($where_today)->count();

		//视频总数
		$video_count = Db::name('video')->count();
		//消息总数
		$all_message_count = Db::name('news')->count();

		//近7天会员数
		$seven_member_count = Db::name('user')->where($where_week)->count();
		//近7天视频数
		$seven_video_money = Db::name('video')->where($where_week)->count();
		//近7天消息
		$seven_message_money = Db::name('news')->where($where_week)->count();

		$data['data'] = [
			'today_member_count' =>  $today_member_count,
			'today_pay_member_count' =>  $today_pay_member_count,
			'today_video_released' =>  $today_video_released,
			'today_company_released' =>  $today_company_released,
			'today_seeker_released' =>  $today_seeker_released,
			'today_message' =>  $today_message,
			'member_count' =>  $member_count, //客户总数
			'video_count' => $video_count,
			'all_message_count' =>  $all_message_count,
			'seven_member_count' =>  $seven_member_count,
			'seven_video_money' => $seven_video_money,
			'seven_message_money' =>  $seven_message_money,
		];
		ajaxReturn($data);
	}
}
