<?php
/**
 * MyBB 1.4
 * Copyright � 2008 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybboard.net
 * License: http://www.mybboard.net/about/license
 *
 * $Id$
 */

$uid_list = $aid_list = $pid_list = $tid_list = $fid_list = $eid_list = array();

/**
 * Fetch a users activity and any corresponding details from their location.
 *
 * @param string The location (URL) of the user.
 * @return array Array of location and activity information
 */
function fetch_wol_activity($location)
{
	global $uid_list, $aid_list, $pid_list, $tid_list, $fid_list, $eid_list, $plugins, $user, $parameters;

	$user_activity = array();

	$split_loc = explode(".php", $location);
	if($split_loc[0] == $user['location'])
	{
		$filename = '';
	}
	else
	{
		$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	}
	if($split_loc[1])
	{
		$temp = explode("&amp;", my_substr($split_loc[1], 1));
		foreach($temp as $param)
		{
			$temp2 = explode("=", $param, 2);
			$parameters[$temp2[0]] = $temp2[1];
		}
	}

	switch($filename)
	{
		case "announcements":
			if(is_numeric($parameters['fid']))
			{
				$fid_list[] = $parameters['fid'];
			}
			$user_activity['activity'] = "announcements";
			$user_activity['fid'] = $parameters['fid'];
			break;
		case "attachment":
			if(is_numeric($parameters['aid']))
			{
				$aid_list[] = $parameters['aid'];
			}
			$user_activity['activity'] = "attachment";
			$user_activity['aid'] = $parameters['aid'];
			break;
		case "calendar":
			if($parameters['action'] == "event")
			{
				if(is_numeric($parameters['eid']))
				{
					$eid_list[] = $parameters['eid'];
				}
				$user_activity['activity'] = "calendar_event";
				$user_activity['eid'] = $parameters['eid'];
			}
			elseif($parameters['action'] == "addevent" || $parameters['action'] == "do_addevent")
			{
				$user_activity['activity'] = "calendar_addevent";
			}
			elseif($parameters['action'] == "editevent" || $parameters['action'] == "do_editevent")
			{
				$user_activity['activity'] = "calendar_editevent";
			}
			else
			{
				$user_activity['activity'] = "calendar";
			}
			break;
		case "editpost":
			$user_activity['activity'] = "editpost";
			break;
		case "forumdisplay":
			if(is_numeric($parameters['fid']))
			{
				$fid_list[] = $parameters['fid'];
			}
			$user_activity['activity'] = "forumdisplay";
			$user_activity['fid'] = $parameters['fid'];
			break;
		case "index":
		case '':
			$user_activity['activity'] = "index";
			break;
		case "managegroup":
			$user_activity['activity'] = "managegroup";
			break;
		case "member":
			if($parameters['action'] == "activate")
			{
				$user_activity['activity'] = "member_activate";
			}
			elseif($parameters['action'] == "register" || $parameters['action'] == "do_register")
			{
				$user_activity['activity'] = "member_register";
			}
			elseif($parameters['action'] == "login" || $parameters['action'] == "do_login")
			{
				$user_activity['activity'] = "member_login";
			}
			elseif($parameters['action'] == "logout")
			{
				$user_activity['activity'] = "member_logout";
			}
			elseif($parameters['action'] == "profile")
			{
				$user_activity['activity'] = "member_profile";
				if(is_numeric($parameters['uid']))
				{
					$uid_list[] = $parameters['uid'];
				}
				$user_activity['uid'] = $parameters['uid'];
			}
			elseif($parameters['action'] == "emailuser" || $parameters['action'] == "do_emailuser")
			{
				$user_activity['activity'] = "member_emailuser";
			}
			elseif($parameters['action'] == "rate" || $parameters['action'] == "do_rate")
			{
				$user_activity['activity'] = "member_rate";
			}
			elseif($parameters['action'] == "resendactivation" || $parameters['action'] == "do_resendactivation")
			{
				$user_activity['activity'] = "member_resendactivation";
			}
			elseif($parameters['action'] == "lostpw" || $parameters['action'] == "do_lostpw" || $parameters['action'] == "resetpassword")
			{
				$user_activity['activity'] = "member_lostpw";
			}
			else
			{
				$user_activity['activity'] = "member";
			}
			break;
		case "memberlist":
			$user_activity['activity'] = "memberlist";
			break;
		case "misc":
			$accepted_parameters = array("markread", "help", "buddypopup", "smilies", "syndication", "imcenter", "dstswitch");
			if($parameters['action'] == "whoposted")
			{
				if(is_numeric($parameters['tid']))
				{
					$tid_list[] = $parameters['tid'];
				}
				$user_activity['activity'] = "misc_whoposted";
				$user_activity['tid'] = $parameters['tid'];
			}			
			elseif(in_array($parameters['action'], $accepted_parameters))
			{
				$user_activity['activity'] = "misc_".$parameters['action'];
			}
			else
			{
				$user_activity['activity'] = "misc";
			}
			break;
		case "modcp":
			$accepted_parameters = array("modlogs", "announcements", "finduser", "warninglogs", "ipsearch");
			
			foreach($accepted_parameters as $action)
			{
				if($parameters['action'] == $action)
				{
					$user_activity['activity'] = "modcp_".$action;
					break;
				}
			}
			
			$accepted_parameters = array();
			$accepted_parameters['report'] = array("do_reports", "reports", "allreports");			
			$accepted_parameters['new_annoucement'] = array("do_new_announcement", "new_announcement");
			$accepted_parameters['delete_announcement'] = array("do_delete_announcement", "delete_announcement");
			$accepted_parameters['edit_announcement'] = array("do_edit_announcement", "edit_announcement");
			$accepted_parameters['mod_queue'] = array("do_modqueue", "modqueue");
			$accepted_parameters['editprofile'] = array("do_editprofile", "editprofile");
			$accepted_parameters['banning'] = array("do_banuser", "banning", "liftban", "banuser");
			
			foreach($accepted_parameters as $name => $actions)
			{
				if(in_array($parameters['action'], $actions))
				{					
					$user_activity['activity'] = "modcp_".$name;
					break;
				}
			}
			
			if(!$user_activity['activity'])
			{
				$user_activity['activity'] = "modcp";
			}
			break;
		case "moderation":
			$user_activity['activity'] = "moderation";
			break;
		case "newreply":
			if(is_numeric($parameters['pid']))
			{
				$pid_list[] = $parameters['pid'];
				$user_activity['activity'] = "newreply";
				$user_activity['pid'] = $parameters['pid'];
			}
			else
			{
				if(is_numeric($parameters['tid']))
				{
					$tid_list[] = $parameters['tid'];
				}
				$user_activity['activity'] = "newreply";
				$user_activity['tid'] = $parameters['tid'];
			}
			break;
		case "newthread":
			if(is_numeric($parameters['fid']))
			{
				$fid_list[] = $parameters['fid'];
			}
			$user_activity['activity'] = "newthread";
			$user_activity['fid'] = $parameters['fid'];
			break;
		case "online":
			if($parameters['action'] == "today")
			{
				$user_activity['activity'] = "woltoday";
			}
			else
			{
				$user_activity['activity'] = "wol";
			}
			break;
		case "polls":
			// Make the "do" parts the same as the other one.
			if($parameters['action'] == "do_newpoll")
			{
				$user_activity['activity'] = "newpoll";
			}
			elseif($parameters['action'] == "do_editpoll")
			{
				$user_activity['activity'] = "editpoll";
			}
			else
			{
				$accepted_parameters = array("do_editpoll", "editpoll", "newpoll", "do_newpoll", "showresults", "vote");
			
				foreach($accepted_parameters as $action)
				{
					if($parameters['action'] == $action)
					{
						$user_activity['activity'] = $action;
						break;
					}
				}
				
				if(!$user_activity['activity'])
				{
					$user_activity['activity'] = "showresults";
				}
			}
			break;
		case "printthread":
			if(is_numeric($parameters['tid']))
			{
				$tid_list[] = $parameters['tid'];
			}
			$user_activity['activity'] = "printthread";
			$user_activity['tid'] = $parameters['tid'];
			break;
		case "private":
			if($parameters['action'] == "send" || $parameters['action'] == "do_send")
			{
				$user_activity['activity'] = "private_send";
			}
			elseif($parameters['action'] == "show")
			{
				$user_activity['activity'] = "private_read";
			}
			elseif($parameters['action'] == "folders" || $parameters['action'] == "do_folders")
			{
				$user_activity['activity'] = "private_folders";
			}
			else
			{
				$user_activity['activity'] = "private";
			}
			break;
		case "ratethread":
			$user_activity['activity'] = "ratethread";
			break;
		case "report":
			$user_activity['activity'] = "report";
			break;
		case "reputation":
			$user_activity['activity'] = "reputation";
			break;
		case "search":
			$user_activity['activity'] = "search";
			break;
		case "sendthread":
			if(is_numeric($parameters['tid']))
			{
				$tid_list[] = $parameters['tid'];
			}
			$user_activity['activity'] = "sendthread";
			$user_activity['tid'] = $parameters['tid'];
		break;
		case "showteam":
			$user_activity['activity'] = "showteam";
			break;
		case "showthread":
			if(is_numeric($parameters['pid']) && $parameters['action'] == "showpost")
			{
				$pid_list[] = $parameters['pid'];
				$user_activity['activity'] = "showpost";
				$user_activity['pid'] = $parameters['pid'];
			}
			else
			{
				if($parameters['page'])
				{
					$user_activity['page'] = $parameters['page'];
				}
				if(is_numeric($parameters['tid']))
				{
					$tid_list[] = $parameters['tid'];
				}
				$user_activity['activity'] = "showthread";
				$user_activity['tid'] = $parameters['tid'];
			}
			break;
		case "stats":
			$user_activity['activity'] = "stats";
			break;
		case "usercp":
			if($parameters['action'] == "profile" || $parameters['action'] == "do_profile")
			{
				$user_activity['activity'] = "usercp_profile";
			}
			elseif($parameters['action'] == "options" || $parameters['action'] == "do_options")
			{
				$user_activity['activity'] = "usercp_options";
			}
			elseif($parameters['action'] == "password" || $parameters['action'] == "do_password")
			{
				$user_activity['activity'] = "usercp_password";
			}
			elseif($parameters['action'] == "editsig" || $parameters['action'] == "do_editsig")
			{
				$user_activity['activity'] = "usercp_editsig";
			}
			elseif($parameters['action'] == "avatar" || $parameters['action'] == "do_avatar")
			{
				$user_activity['activity'] = "usercp_avatar";
			}
			elseif($parameters['action'] == "editlists" || $parameters['action'] == "do_editlists")
			{
				$user_activity['activity'] = "usercp_editlists";
			}
			elseif($parameters['action'] == "favorites")
			{
				$user_activity['activity'] = "usercp_favorites";
			}
			elseif($parameters['action'] == "subscriptions")
			{
				$user_activity['activity'] = "usercp_subscriptions";
			}
			elseif($parameters['action'] == "notepad" || $parameters['action'] == "do_notepad")
			{
				$user_activity['activity'] = "usercp_notepad";
			}
			else
			{
				$user_activity['activity'] = "usercp";
			}
			break;
		case "usercp2":
			if($parameters['action'] == "addfavorite" || $parameters['action'] == "removefavorite" || $parameters['action'] == "removefavorites")
			{
				$user_activity['activity'] = "usercp2_favorites";
			}
			elseif($parameters['action'] == "addsubscription" || $parameters['action'] == "removesubscription" || $parameters['action'] == "removesubscription")
			{
				$user_activity['activity'] = "usercp2_subscriptions";
			}
			break;
		case "portal":
			$user_activity['activity'] = "portal";
			break;
		case "warnings":
			if($parameters['action'] == "warn" || $parameters['action'] == "do_warn")
			{
				$user_activity['activity'] = "warnings_warn";
			}
			elseif($parameters['action'] == "do_revoke")
			{
				$user_activity['activity'] = "warnings_revoke";
			}
			elseif($parameters['action'] == "view")
			{
				$user_activity['activity'] == "warnings_view";
			}
			else
			{
				$user_activity['activity'] = "warnings";
			}
			break;
		case "nopermission":
			$user_activity['activity'] = "nopermission";
			break;
		default:
			$user_activity['activity'] = "unknown";
			break;
	}
	
	$user_activity['location'] = htmlspecialchars_uni($location);
	
	$plugins->run_hooks_by_ref("fetch_wol_activity_end", $user_activity);
	
	return $user_activity;
}

/**
 * Builds a friendly named Who's Online location from an "activity" and array of user data. Assumes fetch_wol_activity has already been called.
 *
 * @param array Array containing activity and essential IDs.
 * @return string Location name for the activity being performed.
 */
function build_friendly_wol_location($user_activity)
{
	global $db, $lang, $uid_list, $aid_list, $pid_list, $tid_list, $fid_list, $eid_list, $plugins, $parser, $mybb;
	global $threads, $forums, $forums_linkto, $posts, $events, $usernames, $attachments;

	// Fetch forum permissions for this user
	$unviewableforums = get_unviewable_forums();
	if($unviewableforums)
	{
		$fidnot = " AND fid NOT IN ($unviewableforums)";
	}

	// Fetch any users
	if(!is_array($usernames) && count($uid_list) > 0)
	{
		$uid_sql = implode(",", $uid_list);
		$query = $db->simple_select("users", "uid,username", "uid IN ($uid_sql)");
		while($user = $db->fetch_array($query))
		{
			$usernames[$user['uid']] = $user['username'];
		}
	}
	
	// Fetch any attachments
	if(!is_array($attachments) && count($aid_list) > 0)
	{
		$aid_sql = implode(",", $aid_list);
		$query = $db->simple_select("attachments", "aid,pid", "aid IN ($aid_sql)");
		while($attachment = $db->fetch_array($query))
		{
			$attachments[$attachment['aid']] = $attachment['pid'];
			$pid_list[] = $attachment['pid'];
		}
	}

	// Fetch any posts
	if(!is_array($posts) && count($pid_list) > 0)
	{
		$pid_sql = implode(",", $pid_list);
		$query = $db->simple_select("posts", "pid,tid", "pid IN ($pid_sql) $fidnot");
		while($post = $db->fetch_array($query))
		{
			$posts[$post['pid']] = $post['tid'];
			$tid_list[] = $post['tid'];
		}
	}

	// Fetch any threads
	if(!is_array($threads) && count($tid_list) > 0)
	{
		$tid_sql = implode(",", $tid_list);
		$query = $db->simple_select("threads", "fid,tid,subject,visible", "tid IN($tid_sql) $fidnot $visible");
		while($thread = $db->fetch_array($query))
		{
			if(is_moderator($thread['fid']) || $thread['visible'] != '0')
			{
				$threads[$thread['tid']] = htmlspecialchars_uni($parser->parse_badwords($thread['subject']));
				$fid_list[] = $thread['fid'];
			}
		}
	}

	// Fetch any forums
	if(!is_array($forums) && count($fid_list) > 0)
	{
		$fid_sql = implode(",", $fid_list);
		$query = $db->simple_select("forums", "fid,name,linkto", "fid IN ($fid_sql) $fidnot");
		while($forum = $db->fetch_array($query))
		{
			$forums[$forum['fid']] = $forum['name'];
			$forums_linkto[$forum['fid']] = $forum['linkto'];
		}
	}

	// And finaly any events
	if(!is_array($events) && count($eid_list) > 0)
	{
		$eid_sql = implode(",", $eid_list);
		$query = $db->simple_select("events", "eid,name", "eid IN ($eid_sql)");
		while($event = $db->fetch_array($query))
		{
			$events[$event['eid']] = htmlspecialchars_uni($parser->parse_badwords($event['name']));
		}
	}

	// Now we've got everything we need we can put a name to the location
	switch($user_activity['activity'])
	{
		// announcement.php functions
		case "announcements":
			if($forums[$user_activity['fid']])
			{
				$location_name = $lang->sprintf($lang->viewing_announcements, get_forum_link($user_activity['fid']), $forums[$user_activity['fid']]);
			}
			else
			{
				$location_name = $lang->viewing_announcements2;
			}
			break;
		// attachment.php actions
		case "attachment":
			$pid = $attachments[$user_activity['aid']];
			$tid = $posts[$pid];
			if($threads[$tid])
			{
				$location_name = $lang->sprintf($lang->viewing_attachment2, $user_activity['aid'], $threads[$tid], get_thread_link($tid));
			}
			else
			{
				$location_name = $lang->viewing_attachment;
			}
			break;
		// calendar.php functions
		case "calendar":
			$location_name = $lang->viewing_calendar;
			break;
		case "calendar_event":
			if($events[$user_activity['eid']])
			{
				$location_name = $lang->sprintf($lang->viewing_event2, get_event_link($user_activity['eid']), $events[$user_activity['eid']]);
			}
			else
			{
				$location_name = $lang->viewing_event;
			}
			break;
		case "calendar_addevent":
			$location_name = $lang->adding_event;
			break;
		case "calendar_editevent":
			$location_name = $lang->editing_event;
			break;
		// editpost.php functions
		case "editpost":
			$location_name = $lang->editing_post;
			break;
		// forumdisplay.php functions
		case "forumdisplay":
			if($forums[$user_activity['fid']])
			{
				if($forums_linkto[$user_activity['fid']])
				{
					$location_name = $lang->sprintf($lang->forum_redirect_to, get_forum_link($user_activity['fid']), $forums[$user_activity['fid']]);
				}
				else
				{
					$location_name = $lang->sprintf($lang->viewing_forum2, get_forum_link($user_activity['fid']), $forums[$user_activity['fid']]);
				}
			}
			else
			{
				$location_name = $lang->viewing_forum;
			}
			break;
		// index.php functions
		case "index":
			$location_name = $lang->sprintf($lang->viewing_index, $mybb->settings['bbname']);
			break;
		// managegroup.php functions
		case "managegroup":
			$location_name = $lang->managing_group;
			break;
		// member.php functions
		case "member_activate":
			$location_name = $lang->activating_account;
			break;
		case "member_profile":
			if($usernames[$user_activity['uid']])
			{
				$location_name = $lang->sprintf($lang->viewing_profile2, get_profile_link($user_activity['uid']), $usernames[$user_activity['uid']]);
			}
			else
			{
				$location_name = $lang->viewing_profile;
			}
			break;
		case "member_register":
			$location_name = $lang->registering;
			break;
		case "member":
		case "member_login":
			// Guest or member?
			if($mybb->user['uid'] == 0)
			{
				$location_name = $lang->logging_in;
			}
			else
			{
				$location_name = $lang->logging_in_plain;
			}
			break;
		case "member_logout":
			$location_name = $lang->logging_out;
			break;
		case "member_emailuser":
			$location_name = $lang->emailing_user;
			break;
		case "member_rate":
			$location_name = $lang->rating_user;
			break;
		case "member_resendactivation":
			$location_name = $lang->resending_account_activation;
			break;
		case "member_lostpw":
			$location_name = $lang->member_lostpw;
			break;
		// memberlist.php functions
		case "memberlist":
			$location_name = $lang->viewing_memberlist;
			break;
		// misc.php functions
		case "misc_dstswitch":
			$location_name = $lang->changing_dst;
			break;
		case "misc_whoposted":
			if($threads[$user_activity['tid']])
			{
				$location_name = $lang->sprintf($lang->viewing_whoposted2, get_thread_link($user_activity['tid']), $threads[$user_activity['tid']]);
			}
			else
			{
				$location_name = $lang->viewing_whoposted;
			}
			break;
		case "misc_markread":
			$location_name = $lang->marking_read;
			break;
		case "misc_help":
			$location_name = $lang->viewing_helpdocs;
			break;
		case "misc_buddypopup":
			$location_name = $lang->viewing_buddylist;
			break;
		case "misc_smilies":
			$location_name = $lang->viewing_smilies;
			break;
		case "misc_syndication":
			$location_name = $lang->viewing_syndication;
			break;
		case "misc_imcenter":
			$location_name = $lang->viewing_imcenter;
			break;						
		// modcp.php functions
		case "modcp_modlogs":
			$location_name = $lang->viewing_modlogs;
			break;
		case "modcp_announcements":
			$location_name = $lang->managing_announcements;
			break;
		case "modcp_finduser":
			$location_name = $lang->search_for_user;
			break;
		case "modcp_warninglogs":
			$location_name = $lang->managing_warninglogs;
			break;
		case "modcp_ipsearch":
			$location_name = $lang->searching_ips;
			break;
		case "modcp_report":
			$location_name = $lang->viewing_reports;
			break;
		case "modcp_new_announcement":
			$location_name = $lang->adding_announcement;
			break;
		case "modcp_delete_announcement":
			$location_name = $lang->deleting_announcement;
			break;
		case "modcp_edit_announcement":
			$location_name = $lang->editing_announcement;
			break;
		case "modcp_mod_queue":
			$location_name = $lang->managing_modqueue;
			break;
		case "modcp_editprofile":
			$location_name = $lang->editing_user_profiles;
			break;
		case "modcp_banning":
			$location_name = $lang->managing_bans;
			break;
		case "modcp":
			$location_name = $lang->viewing_modcp;
			break;
		// moderation.php functions
		case "moderation":
			$location_name = $lang->using_modtools;
			break;
		// newreply.php functions
		case "newreply":
			if($user_activity['pid'])
			{
				$user_activity['tid'] = $posts[$user_activity['pid']];
			}
			if($threads[$user_activity['tid']])
			{
				$location_name = $lang->sprintf($lang->replying_thread2, get_thread_link($user_activity['tid']), $threads[$user_activity['tid']]);
			}
			else
			{
				$location_name = $lang->replying_thread;
			}
			break;
		// newthread.php functions
		case "newthread":
			if($forums[$user_activity['fid']])
			{
				$location_name = $lang->sprintf($lang->posting_thread2, get_forum_link($user_activity['fid']), $forums[$user_activity['fid']]);
			}
			else
			{
				$location_name = $lang->posting_thread;
			}
			break;
		// online.php functions
		case "wol":
			$location_name = $lang->viewing_wol;
			break;
		case "woltoday":
			$location_name = $lang->viewing_woltoday;
			break;
		// polls.php functions
		case "newpoll":
			$location_name = $lang->creating_poll;
			break;
		case "editpoll":
			$location_name = $lang->editing_poll;
			break;
		case "showresults":
			$location_name = $lang->viewing_pollresults;
			break;
		case "vote":
			$location_name = $lang->voting_poll;
			break;
		// printthread.php functions
		case "printthread":
			if($threads[$user_activity['tid']])
			{
				$location_name = $lang->sprintf($lang->printing_thread2, get_thread_link($user_activity['tid']), $threads[$user_activity['tid']]);
			}
			else
			{
				$location_name = $lang->printing_thread;
			}
			break;
		// private.php functions
		case "private_send":
			$location_name = $lang->sending_pm;
			break;
		case "private_read":
			$location_name = $lang->reading_pm;
			break;
		case "private_folders":
			$location_name = $lang->editing_pmfolders;
			break;
		case "private":
			$location_name = $lang->using_pmsystem;
			break;
		/* Ratethread functions */
		case "ratethread":
			$location_name = $lang->rating_thread;
			break;
		// report.php functions
		case "report":
			$location_name = $lang->reporting_post;
			break;
		// reputation.php functions
		case "reputation":
			$location_name = $lang->giving_reputation;
			break;
		// search.php functions
		case "search":
			$location_name = $lang->sprintf($lang->searching_forum, $mybb->settings['bbname']);
			break;
		// showthread.php functions
		case "showthread":
			if($threads[$user_activity['tid']])
			{
				$pagenote = '';
				$location_name = $lang->sprintf($lang->reading_thread2, get_thread_link($user_activity['tid']), $threads[$user_activity['tid']], $pagenote);
			}
			else
			{
				$location_name = $lang->reading_thread;
			}
			break;
		// showteam.php functions
		case "showteam":
			$location_name = $lang->viewing_team;
			break;
		// stats.php functions
		case "stats":
			$location_name = $lang->viewing_stats;
			break;
		// usercp.php functions
		case "usercp_profile":
			$location_name = $lang->updating_profile;
			break;
		case "usercp_options":
			$location_name = $lang->updating_options;
			break;
		case "usercp_editsig":
			$location_name = $lang->editing_signature;
			break;
		case "usercp_avatar":
			$location_name = $lang->changing_avatar;
			break;
		case "usercp_subscriptions":
			$location_name = $lang->viewing_subscriptions;
			break;
		case "usercp_favorites":
			$location_name = $lang->viewing_favorites;
			break;
		case "usercp_notepad":
			$location_name = $lang->editing_pad;
			break;
		case "usercp_password":
			$location_name = $lang->editing_password;
			break;
		case "usercp":
			$location_name = $lang->user_cp;
			break;
		case "usercp2_favorites":
			$location_name = $lang->managing_favorites;
			break;
		case "usercp2_subscriptions":
			$location_name = $lang->managing_subscriptions;
			break;
		case "portal":
			$location_name = $lang->viewing_portal;
			break;
		// sendthread.php functions
		case "sendthread":
			$location_name = $lang->sending_thread;
			break;
		// warnings.php functions
		case "warnings_revoke":
			$location_name = $lang->revoking_warning;
			break;
		case "warnings_warn":
			$location_name = $lang->warning_user;
			break;
		case "warnings_view":
			$location_name = $lang->viewing_warning;
			break;
		case "warnings":
			$location_name = $lang->managing_warnings;
			break;
	}
	
	$plugin_array = array('user_activity' => &$user_activity, 'location_name' => &$location_name);
	$plugins->run_hooks_by_ref("build_friendly_wol_location_end", $plugin_array);
	
	if($user_activity['nopermission'] == 1)
	{
		$location_name = $lang->viewing_noperms;
	}
	
	if(!$location_name)
	{
		$location_name = $lang->sprintf($lang->unknown_location, $user_activity['location']);
	}

	return $location_name;
}

/**
 * Build a Who's Online row for a specific user
 *
 * @param array Array of user information including activity information
 * @return string Formatted online row
 */
function build_wol_row($user)
{
	global $mybb, $lang, $templates, $themes, $session;

	// We have a registered user
	if($user['uid'] > 0)
	{
		// Only those with "canviewwolinvis" permissions can view invisible users
		if($user['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1 || $user['uid'] == $mybb->user['uid'])
		{
			// Append an invisible mark if the user is invisible
			if($user['invisible'] == 1)
			{
				$invisible_mark = "*";
			}
			else
			{
				$invisible_mark = '';
			}

			$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
			$online_name = build_profile_link($user['username'], $user['uid']).$invisible_mark;
		}
	}
	// We have a bot
	elseif($user['bot'])
	{
		$online_name = format_name($user['bot'], $user['usergroup']);
	}
	// Otherwise we've got a plain old guest
	else
	{
		$online_name = format_name($lang->guest, 1);
	}

	$online_time = my_date($mybb->settings['timeformat'], $user['time']);
	
	// Fetch the location name for this users activity
	$location = build_friendly_wol_location($user['activity']);

	// Can view IPs, then fetch the IP template
	if($mybb->usergroup['canviewonlineips'] == 1)
	{
		eval("\$user_ip = \"".$templates->get("online_row_ip")."\";");
	}
	else
	{
		$user['ip'] = '';
	}

	// And finally if we have permission to view this user, return the completed online row
	if($user['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1 || $user['uid'] == $mybb->user['uid'])
	{
		eval("\$online_row = \"".$templates->get("online_row")."\";");
	}
	return $online_row;
}
?>