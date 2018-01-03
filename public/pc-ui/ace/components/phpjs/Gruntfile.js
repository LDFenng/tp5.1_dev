module.exports = function(grunt) {
	// 配置
    grunt.initConfig({
        pkg : grunt.file.readJSON('package.json'),
        concat : {
            domop : {
                src: [
				
				//----------var-------------
				'functions/var/doubleval.js', 
				'functions/var/empty.js', 
				'functions/var/floatval.js', 
				'functions/var/gettype.js', 
				'functions/var/intval.js', 
				'functions/var/is_array.js', 
				'functions/var/is_bool.js', 
				'functions/var/is_double.js', 
				'functions/var/is_float.js', 
				'functions/var/is_int.js', 
				'functions/var/is_integer.js', 
				'functions/var/is_long.js', 
				'functions/var/is_null.js', 
				'functions/var/is_numeric.js', 
				'functions/var/is_object.js', 
				'functions/var/is_string.js', 
				'functions/var/isset.js', 
				'functions/var/strval.js', 
				
				//---------funchand---------
				'functions/funchand/function_exists.js',
				
				//---------array-----------
				'functions/array/array.js', 
				'functions/array/array_key_exists.js', 
				'functions/array/array_keys.js', 
				'functions/array/array_merge.js	',
				'functions/array/array_merge_recursive.js', 			
				'functions/array/in_array.js', 
				'functions/array/count.js', 
				
				//---------strings-----------				
				'functions/strings/ltrim.js', 
				'functions/strings/rtrim.js', 			
				'functions/strings/trim.js', 
				'functions/strings/strtolower.js',
				'functions/strings/strtoupper.js', 				
				'functions/strings/strpos.js',
				'functions/strings/stripos.js',
				'functions/strings/strripos.js', 
				'functions/strings/strrpos.js',					
				'functions/strings/explode.js',
				'functions/strings/split.js',
				'functions/strings/substr.js',
				'functions/strings/htmlentities.js',
				'functions/strings/htmlspecialchars.js',				
				'functions/strings/htmlspecialchars_decode.js',
				
				
				
				//---------datetime----------
				'functions/datetime/checkdate.js', 
				'functions/datetime/date.js', 
				'functions/datetime/date_parse.js',
				'functions/datetime/getdate.js',
				'functions/datetime/strtotime.js',
				'functions/datetime/time.js',

				//----------url--------------
				'functions/url/base64_decode.js',
				'functions/url/base64_encode.js',
				'functions/url/get_headers.js',
				'functions/url/get_meta_tags.js',
				'functions/url/http_build_query.js',
				'functions/url/parse_url.js',
				'functions/url/rawurldecode.js',
				'functions/url/rawurlencode.js',
				'functions/url/urldecode.js',
				'functions/url/urlencode.js',
			
				],
                dest: 'dest/phpjs_util.js'
            }
        },
        uglify : {
            options : {
                banner : '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
            },
            build : {
                src : 'dest/phpjs_util.js',
                dest : 'dest/phpjs_util.min.js'
            }
        }
    });
    // 载入concat和uglify插件，分别对于合并和压缩
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    // 注册任务
    grunt.registerTask('default', ['concat', 'uglify']);
};