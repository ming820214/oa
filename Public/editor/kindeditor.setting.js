		var editor;
		function editor_init(){
			if (is_mobile())
			{
				settings={
					resizeType:1,	
					filterMode : true,
					uploadJson:upload_url,
					width :'100%',
					items : [],
					afterBlur : function() {
						this.sync();
					}
				}
			}else{
				settings={
					resizeType:1,	
					filterMode : true,
					uploadJson:upload_url,					
					width :'100%',
					afterBlur : function() {
						this.sync();
					}
				}
			}
		editor = new KindEditor.create(".editor",settings);
		}