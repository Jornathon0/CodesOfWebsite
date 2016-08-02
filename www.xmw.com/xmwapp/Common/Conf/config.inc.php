<?php
return array(
    'URL_MODEL' => 0,
    'URL_CASE_INSENSITIVE' => true,
    'SESSION_AUTO_START' => true,
    'URL_HTML_SUFFIX' => 'html',
    'SHOW_PAGE_TRACE' => false,
    'VAR_FILTERS' => 'stripslashes,strip_tags',
    'DB_FIELDTYPE_CHECK' => true, // 开启字段类型验证  
    'SESSION_AUTO_START' => true,
	'URL_ROUTER_ON'   => true, //开启路由
	'JIAMI_KEY' => 'xmwcom',//加密口令
 	'URL_ROUTE_RULES' => array( //定义路由规则
 			'/^account\/signup(.html)?$/'  => 'register/signup', // add:king
 			'/^isemail(.html)?$/'  => 'public/isemail', // add:king
 			'/^isyzm(.html)?$/'  => 'public/isyzm', // add:king
 			'/^getverify(.html)?$/'  => 'public/getVerify', // add:king
 			'/^regsuccess(.html)?$/'  => 'register/regSuccess', // add:king
 			'activation'  => 'public/activation', // add:king
 			'/^LoginJson(.html)?$/'  => 'login/login', // add:king
 			'/^GetUserInfo(.html)?$/'  => 'account/GetUserInfo', // add:king
 			'/^logout(.html)?$/'  => 'login/logout', // add:king
 			'/^broker(.html)?$/'  => 'public/broker', // add:king
			'/^yanzhenma(.html)?$/'  => 'login/yanzhenma',
			'/^sendsms(.html)?$/'  => 'register/sendsms',//发送短信

			//add:qiuyifeng
			'/^escrow(.html)?$/'  => 'escrow/index',
			'/^escrowoperation(.html)?$/'  => 'escrow/operation',
			'/^account\/dealdetail\/(\d+)?$/'  => 'account/dealdetail?datetime=:1',
			'/^account\/deals\/(\w+)?$/'  => 'account/deals?act=:1',
			
			'/^procurement(.html)?$/'  => 'purchase/index',
			'/^procurementoperation(.html)?$/'  => 'purchase/operation',
			
			'/^terminal(.html)?$/'  => 'terminal/index',
			'/^terminaloperation(.html)?$/'  => 'terminal/operation',
 			//'/^account\/terminal?$/'  => 'account/terminal',
			#卖域名
			'/^sell-domains(.html)?$/'  => 'selldomains/sellDomains',
			'/^account\/group(.html)?$/'  => 'selldomains/group',//域名分组
				//'/^account\/group\/add(.html)?$/'  => 'selldomains/group',//域名分组添加
				//'/^account\/group\/update(.html)?$/'  => 'selldomains/group',//域名分组修改
			'/^account\/adddomains(.html)?$/'  => 'selldomains/adddomains',//添加域名页
			'/^account\/adddomainsTip(.html)?$/'  => 'selldomains/adddomainsTip',//添加域名后的提示页面
				'/^account\/adddomains\/pl(.html)?$/'  => 'selldomains/adddomains_pl',//添加批量交易页
			'/^account\/pldomain(.html)?$/'  => 'selldomains/pldomain',//我的批量交易页面
			'/^account\/sellnegotiation(.html)?$/'  => 'selldomains/sellnegotiation',//我收到的报价页面
			//'/^account\/fastbid(.html)?$/'  => 'selldomains/fastbid',//极速竞价管理页面
			'/^account\/sellnow(.html)?$/'  => 'selldomains/sellnow',//卖方限时抢购页面
				'/^account\/sellnow\/check(.html)?$/'  => 'selldomains/sellnow?act=check',//卖方限时抢购审核页面
				'/^account\/sellnow\/wait(.html)?$/'  => 'selldomains/sellnow?act=wait',//卖方限时抢购等待页面
				'/^account\/sellnow\/end(.html)?$/'  => 'selldomains/sellnow?act=end',//卖方限时抢购已出售页面
			//'/^account\/buynow(.html)?$/'  => 'selldomains/sellnow',//卖方限时抢购页面
				'/^account\/buynow\/check(.html)?$/'  => 'account/buynow?act=check',//买方限时抢购审核页面
				'/^account\/buynow\/wait(.html)?$/'  => 'account/buynow?act=wait',//买方限时抢购等待页面
				'/^account\/buynow\/end(.html)?$/'  => 'account/buynow?act=end',//买方限时抢购已出售页面
			'/^account\/aprice(.html)?$/'  => 'selldomains/aprice',//一口价管理页面
				'/^account\/aprice\/add(.html)?$/'  => 'selldomains/aprice_add',//一口价管理页面
				'/^account\/pl\/ykj(.html)?$/'  => 'selldomains/pl_ykj',//一口价展示和管理
			'/^account\/highgrade(.html)?$/'  => 'selldomains/highgrade',//我的优质域名页面
			'/^account\/highgradeinf(.html)?$/'  => 'selldomains/highgradeinf',//异步获取优质域名数据
				'/^account\/highgrade\/selling(.html)?$/'  => 'selldomains/highgrade?act=selling',//我的优质域名-出售中
				'/^account\/highgrade\/down(.html)?$/'  => 'selldomains/highgrade?act=down',//我的优质域名-已下架
				'/^account\/highgrade\/sold(.html)?$/'  => 'selldomains/highgrade?act=sold',//我的优质域名-已出售
				
			'/^account\/AddDomainCheck(.html)?$/'  => 'selldomains/AddDomainCheck',//添加域名handle
			'/^account\/withcheck(.html)?$/'  => 'selldomains/withcheck',//待审核域名页
			'/^account\/withcheckinf(.html)?$/'  => 'selldomains/withcheckinf',//异步获取验证域名数据
			'/^account\/checkwithdomain(.html)?$/'  => 'selldomains/checkwithdomain',//验证域名handle
			'/^account\/CheckDomain(.html)?$/'  => 'selldomains/CheckDomain',//批量验证handle
			'/^account\/allsell(.html)?$/'  => 'selldomains/allsell',//我的域名
				'/^account\/allsell\/(\w+)?$/'  => 'selldomains/allsell?type=:1',
			'/^account\/allsellinf(.html)?$/'  => 'selldomains/allsellinf',//异步获取我的验证域名数据
			#--开通操作相关
			'/^account\/kt\/ykj(.html)?$/'  => 'selldomains/kt?kt=ykj',//开通一口价
			'/^account\/ktOperation(.html)?$/'  => 'selldomains/ktOperation',//开通操作验证
			#--米表操作
			'/^account\/mibiao\/update(.html)?$/'  => 'account/mibiao?act=update',//我的优质域名-已出售
			
			#账户管理相关
			'/^account\/bind\/email(.html)?$/'  => 'account/bind_email',//邮箱绑定
			'/^account\/bind\/protect(.html)?$/'  => 'account/bind_protect',//操作保护
			
			#批量设置相关
			'/^account\/pl_set\/(\w+)?$/'  => 'account/pl_set?act=:1',
			'/^about(.html)?$/'  => 'about/index',
			'/^bargain(.html)?$/'  => 'bargain/index',
			'/^broker(.html)?$/'  => 'broker/index',
			'/^buy-domains(.html)?$/'  => 'buy-domains/index',
			'/^contact(.html)?$/'  => 'contact/index',
			'/^culture(.html)?$/'  => 'culture/index',
			'/^culture(.html)?$/'  => 'culture/index',
			'/^gj(.html)?$/'  => 'gj/index',
			'/^help(.html)?$/'  => 'help/index',
			'/^hotsale(.html)?$/'  => 'hotsale/index',
			'/^pldomain(.html)?$/'  => 'pldomain/index',
			'/^sell-domains(.html)?$/'  => 'sell-domains/index',

 ),
);
