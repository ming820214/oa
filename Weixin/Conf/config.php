<?php
return array(
	//'配置项'=>'配置值'
	'TMPL_L_DELIM'=>'{',
	'TMPL_R_DELIM'=>'}',
	
		'SHOW_PAGE_TRACE'        =>0,  //开启调试模式
		'URL_CASE_INSENSITIVE' =>true,//关闭路径区分大小写
		// 'DB_PREFIX' => 'smeoa_', // 数据库表前缀 
		'DB_PREFIX' => '', // 数据库表前缀 
		'DB_DSN' => 'mysql://root:ihongwen@localhost:1209/hw003',
		'TMPL_TEMPLATE_SUFFIX'=>'.html',
		'VAR_URL_PARAMS'      => '_URL_', // PATHINFO URL参数变量
		'VAR_FILTERS'=>'htmlspecialchars',
		  'CorpID' => 'wx48efe07c32d6e8fa',//企业号id
		  'Secret' => 'JrQ85DM3IQetnZDXTrifzYiDuu1lMYlSE4bSx2SSy3Y0ouh6ltDQRwliUTHRfm0Q',//企业号管理员密钥
		// 'TOKEN_ON'=>true,  // 是否开启令牌验证
		// 'TOKEN_NAME'=>'__hash__',    // 令牌验证的表单隐藏字段名称
		// 'TOKEN_TYPE'=>'md5',  //令牌哈希验证规则 默认为MD5
		// 'TOKEN_RESET'=>true,  //令牌验证出错后是否重置令牌 默认为true
);
?>