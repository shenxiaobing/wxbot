#!/usr/bin/env python
# coding: utf-8
import pyqrcode
import os
import thread
import time
import requests
import json
import urllib
import redis
import math
import sys
import time
import socket
socket.setdefaulttimeout( 30 ) 

def write_log(text):
	file_object = open('./log/get_group.log','a')
	r_str = str(text)
	try:
		now_time = time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
		r_str = now_time+"----"+r_str+"\n"
		file_object.write(r_str)
	finally:
		file_object.close( )

def run(web_input,action,bot_list):
	#action ---> http://127.0.0.1/api/*****/  其中×××对应的就是action,通过action字段来实现自定义的操作在下面主程序编写业务逻辑
	#返回格式如下，code目前为200和500，200为正常，500为异常
	#获取当前登录的微信账号群聊信息
	if action == 'get_group_by_redis':
		start_time = time.time()
		write_log("start")
		try:
			#bot_id = web_input['bot_id']
			redis_r = redis.Redis(host='localhost',port=6379,db=0)
			group_count_list=[]
			for x in bot_list:
				#if x.bot_id == bot_id:
				redis_r.set(x.bot_id+"-name",x.bot.my_account['NickName'])
				"""获取微信群聊信息"""
				group_dic = {}
				group_dic['bot_id'] = x.bot_id
				group_dic['g_m_count'] = 0
				group_dic['g_l_count'] = 0
				group_list = []
				seq = 0
				while 1:
					url = x.bot.base_uri + '/webwxgetcontact?pass_ticket=%s&skey=%s&r=%s&seq=%s' \
								  % (x.bot.pass_ticket, x.bot.skey, int(time.time()),seq)
					r = x.bot.session.post(url, data='{}')
					r.encoding = 'utf-8'
					dic = json.loads(r.text)
					for contact in dic['MemberList']:
						if contact['UserName'].find('@@') != -1:  # 群聊
							group_list.append(contact)
					print "微信seq参数："+str(dic['Seq'])
					seq = dic['Seq']
					if seq == 0:
						break
				"""将微信群50个一组放入到数组中"""
				i = 0
				j = 0
				page = int(math.ceil(len(group_list)/50))
				if page == 0 :
					page = 1
				tmp_list = [[]]*page
				tmp_arr = []
				for group_v in group_list:
					key = x.bot_id+'-'+group_v['UserName']
					redis_r.set(key,group_v['NickName'])
					if j == 50 : 
						tmp_list[i] = tmp_arr
						tmp_arr = []
						i += 1
						j=0
					j += 1
					tmp_arr.append(group_v['UserName'])
				if j < 50:
					tmp_list[i-1] = tmp_arr

				"""获取微信群聊用户"""
				url = x.bot.base_uri + '/webwxbatchgetcontact?type=ex&r=%s&pass_ticket=%s' % (int(time.time()), x.bot.pass_ticket)
				tmp_i = 0
				for tmp_v in tmp_list:
					tmp_i += 1
					print "微信群用户获取" + str(tmp_i)
					params = {
						'BaseRequest': x.bot.base_request,
						"Count": len(tmp_v),
						"List": [{"UserName": v, "EncryChatRoomId": ""} for v in tmp_v]
					}
					r = x.bot.session.post(url, data=json.dumps(params))
					r.encoding = 'utf-8'
					group_member = json.loads(r.text)
					for group_m_v in group_member['ContactList']:
						for member_v in group_m_v['MemberList']:
							key = x.bot_id+'-'+member_v['UserName']
							redis_r.set(key,member_v['NickName'])
							group_dic['g_m_count'] += 1
				group_dic['g_l_count'] = len(group_list)
				group_count_list.append(group_dic)
				write_log(x.bot_id+'-group:'+str(group_dic['g_l_count'])+'-group_member'+str(group_dic['g_m_count']))
			data = {
				'data':group_count_list,'time':time.time() - start_time
			}
			write_log("end")
			return {'code':200,'error_info':'','data':data}
			#return {'code':500,'error_info':'bot_id not found!!','data':''}
		except Exception,e:
			write_log("error")
			return {'code':500,'error_info':str(e),'data':''}
