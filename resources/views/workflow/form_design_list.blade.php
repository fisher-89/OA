<!DOCTYPE HTML>
<html>
<!--form_design_list-->
    <head>
        <title>表单设计</title>
        <meta name="keyword"
              content="ueditor Formdesign plugins,formdesigner,ueditor扩展,web表单设计器,高级表单设计器,Leipi Form Design,web form设计器,web form designer,javascript jquery ueditor php表单设计器,formbuilder">
        <meta name="description"
              content="Ueditor Web Formdesign Plugins 扩展即WEB表单设计器扩展，它通常在、OA系统、问卷调查系统、考试系统、等领域发挥着重要作用，你可以在此基础上任意修改使功能无限强大！">

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="author" content="leipi.org">
        <link href="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/css/bootstrap/css/bootstrap.css?2023')}}" rel="stylesheet"
              type="text/css"/>
        <link href="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/css/site.css?2023')}}" rel="stylesheet" type="text/css"/>
        <script type="text/javascript">
            var _root = 'http://form/index.php?s=/', _controller = 'index';
        </script>
    </head>
    <body>

        <div class="container" style="width:100%;">
            <form method="post" id="saveform" name="saveform"
                  action="{{source(route('workflow.formDesignPreview'))}}">
                <input type="hidden" name="form_id" id="form_id" value="{{$form_id}}">
                <input type="hidden" id="fields" value="{{$data['fields'] or '0'}}" />
                {{ csrf_field() }}
                <div class="row" style="margin-left: 20px;">

                    <div class="well well-small">

                        <p>
                            一栏布局：<br/><br/>
                            <button type="button" onclick="leipiFormDesign.exec('text');" class="btn btn-info">文本框</button>
                            <button type="button" onclick="leipiFormDesign.exec('textarea');" class="btn btn-info">多行文本</button>
                            <button type="button" onclick="leipiFormDesign.exec('select');" class="btn btn-info">下拉菜单</button>
                            <button type="button" onclick="leipiFormDesign.exec('radios');" class="btn btn-info">单选框</button>
                            <button type="button" onclick="leipiFormDesign.exec('checkboxs');" class="btn btn-info">复选框</button>
                            <button type="button" onclick="leipiFormDesign.exec('macros');" class="btn btn-info">宏控件</button>
                            <button type="button" onclick="leipiFormDesign.exec('progressbar');" class="btn btn-info">进度条
                            </button>
                            <button type="button" onclick="leipiFormDesign.exec('qrcode');" class="btn btn-info">二维码</button>
                            <button type="button" onclick="leipiFormDesign.exec('listctrl');" class="btn btn-info">列表控件</button>
                            <button type="button" onclick="leipiFormDesign.exec('mytime');" class="btn btn-info">时间选择
                            </button>
                            <button type="button" onclick="leipiFormDesign.exec('more');" class="btn btn-primary">一起参与...
                            </button>
                        </p>
                    </div>

                </div>
                <div class="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>提醒：</strong>单选框和复选框，如：<code>{|-</code>选项<code>-|}</code>两边边界是防止误删除控件，程序会把它们替换为空，请不要手动删除！
                </div>
                <div class="button_arr" style="float:right;margin-bottom:5px;">
                    <button class="btn btn-primary btn-small" onclick="sendForm('save');return false;">保存</button>
                    <button class="btn btn-info btn-small" onclick="viewForm();return false;">预览</button>
                    <button class="btn btn-danger btn-small" onclick="myclose();return false;">关闭</button>
                </div>

                <div class="row">

                    <div class="span2">
                        <ul class="nav nav-list">
                            <li class="nav-header">两栏布局</li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('text');"
                                   class="btn btn-link">文本框</a></li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('textarea');" class="btn btn-link">多行文本</a>
                            </li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('select');" class="btn btn-link">下拉菜单</a>
                            </li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('radios');"
                                   class="btn btn-link">单选框</a></li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('checkboxs');" class="btn btn-link">复选框</a>
                            </li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('macros');"
                                   class="btn btn-link">宏控件</a></li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('progressbar');"
                                   class="btn btn-link">进度条</a></li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('qrcode');"
                                   class="btn btn-link">二维码</a></li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('listctrl');" class="btn btn-link">列表控件</a>
                            </li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('mytime');" class="btn btn-link">时间选择</a>
                            </li>
                            <li><a href="javascript:void(0);" onclick="leipiFormDesign.exec('more');" class="btn btn-link">一起参与...</a>
                            </li>
                        </ul>

                    </div>
                    <textarea style="display:none" id="formeditor_data">{{$data['template'] or ''}}</textarea>
                    <div class="span10" style="width:90%;">

                        <script id="myFormDesign" type="text/plain" style="width:100%;">


                        </script>
                    </div>


                </div><!--end row-->

            </form>


        </div><!--end container-->



        <script type="text/javascript" charset="utf-8"
        src="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/js/jquery-1.7.2.min.js?2023')}}"></script>

        <script type="text/javascript" charset="utf-8"
        src="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/js/ueditor/ueditor.config.js?2023')}}"></script>
        <script type="text/javascript" charset="utf-8"
        src="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/js/ueditor/ueditor.all.js?2023')}}"></script>
        <script type="text/javascript" charset="utf-8"
        src="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/js/ueditor/lang/zh-cn/zh-cn.js?2023')}}"></script>
        <script type="text/javascript" charset="utf-8"
        src="{{source('js/workflow/Formdesign4_1_Ueditor1_4_3/js/ueditor/formdesign/leipi.formdesign.v4.js?2023')}}"></script>
        <!-- script start-->
        <script type="text/javascript">
                                var formeditor_data = $("#formeditor_data").text();

                                var leipiEditor = UE.getEditor('myFormDesign', {
                                    //allowDivTransToP: false,//阻止转换div 为p
                                    toolleipi: true, //是否显示，设计器的 toolbars
                                    textarea: 'design_content',
                                    //这里可以选择自己需要的工具按钮名称,此处仅选择如下五个
                                    toolbars: [[
                                            'fullscreen', 'source', '|', 'undo', 'redo', '|',
                                            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                                            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                                            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                                            'directionalityltr', 'directionalityrtl', 'indent', '|',
                                            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
                                            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
                                            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
                                            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
                                            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                                            'print', 'preview', 'searchreplace', 'help', 'drafts']],
                                    //focus时自动清空初始化时的内容
                                    //autoClearinitialContent:true,
                                    //关闭字数统计
                                    wordCount: false,
                                    //关闭elementPath
                                    elementPathEnabled: false,
                                    //默认的编辑区域高度
                                    initialFrameHeight: 500,
                                    //initialFrameWidth:1000  //初始化编辑器宽度,默认1000
                                    ///,iframeCssUrl:"css/bootstrap/css/bootstrap.css" //引入自身 css使编辑器兼容你网站css
                                    //更多其他参数，请参考ueditor.config.js中的配置项
                                    initialContent: formeditor_data //重新初始化内容
                                            // 'fontsize':[10, 11, 12, 14, 16, 18, 20, 24, 36,48]
                                            // focus: true//初始化时，是否让编辑器获得焦点true或false


                                });
                                //保存按钮
                                function sendForm(type) {
                                    leipiFormDesign.fnCheckForm(type);
                                }
                                //预览表单
                                function viewForm() {
                                    leipiFormDesign.fnReview();
                                }
                                //关闭按钮
                                function myclose() {
                                    var type = "saveClose";
                                    if (confirm("关闭表单设计器前，保存对表单的修改")) {
                                        sendForm(type);
                                    } else {
                                        window.close();
                                    }

                                }

                                var leipiFormDesign = {
                                    /*执行控件*/
                                    exec: function (method) {
                                        leipiEditor.execCommand(method);
                                    },
                                    /*
                                     Javascript 解析表单
                                     template 表单设计器里的Html内容
                                     fields 字段总数
                                     */
                                    parse_form: function (template, fields) {
                                        //正则  radios|checkboxs|select 匹配的边界 |--|  因为当使用 {} 时js报错
                                        var preg = /(\|-<span(((?!<span).)*leipiplugins=\"(radios|checkboxs|select)\".*?)>(.*?)<\/span>-\||<(img|input|textarea|select).*?(<\/select>|<\/textarea>|\/>))/gi, preg_attr = /(\w+)=\"(.?|.+?)\"/gi, preg_group = /<input.*?\/>/gi;
                                        if (!fields)
                                            fields = 0;
                                        var template_parse = template, template_data = new Array(), add_fields = new Object(), checkboxs = 0;

                                        var pno = 0;
                                        template.replace(preg, function (plugin, p1, p2, p3, p4, p5, p6) {
                                            var parse_attr = new Array(), attr_arr_all = new Object(), name = '', select_dot = '', is_new = false;
                                            var p0 = plugin;
                                            var tag = p6 ? p6 : p4;
                                            //alert(tag + " \n- t1 - "+p1 +" \n-2- " +p2+" \n-3- " +p3+" \n-4- " +p4+" \n-5- " +p5+" \n-6- " +p6);

                                            if (tag == 'radios' || tag == 'checkboxs') {
                                                plugin = p2;
                                            } else if (tag == 'select') {
                                                plugin = plugin.replace('|-', '');
                                                plugin = plugin.replace('-|', '');
                                            }
                                            plugin.replace(preg_attr, function (str0, attr, val) {
                                                if (attr == 'name') {
                                                    if (val == 'leipiNewField') {
                                                        is_new = true;
                                                        fields++;
                                                        val = 'data_' + fields;
                                                    }
                                                    name = val;
                                                }

                                                if (tag == 'select' && attr == 'value') {
                                                    if (!attr_arr_all[attr])
                                                        attr_arr_all[attr] = '';
                                                    attr_arr_all[attr] += select_dot + val;
                                                    select_dot = ',';
                                                } else {
                                                    attr_arr_all[attr] = val;
                                                }
                                                var oField = new Object();
                                                oField[attr] = val;
                                                parse_attr.push(oField);
                                            })
                                            /*alert(JSON.stringify(parse_attr));return;*/
                                            if (tag == 'checkboxs') /*复选组  多个字段 */
                                            { 
                                                plugin = p0;
                                                plugin = plugin.replace('|-', '');
                                                plugin = plugin.replace('-|', '');
                                                var name = 'checkboxs_' + checkboxs;
                                                attr_arr_all['parse_name'] = name;
                                                attr_arr_all['name'] = '';
                                                attr_arr_all['value'] = '';

                                                attr_arr_all['content'] = '<span leipiplugins="checkboxs"  title="' + attr_arr_all['title'] + '">';
                                                var dot_name = '', dot_value = '';
                                                
                                                var tmp_fields = fields;//字段数
                                                tmp_fields++;//用于复选框的name值
                                                
                                                p5.replace(preg_group, function (parse_group) {
                                                    var is_new = false, option = new Object();
                                                    parse_group.replace(preg_attr, function (str0, k, val) {
                                                        if (k == 'name') {
                                                            if (val == 'leipiNewField') {
                                                                is_new = true;
                                                                val = 'data_' + tmp_fields+'[]';
                                                                fields = tmp_fields;//字段数重新附值回来；
                                                            }

                                                            attr_arr_all['name'] += dot_name + val;
                                                            dot_name = ',';

                                                        } else if (k == 'value') {
                                                            attr_arr_all['value'] += dot_value + val;
                                                            dot_value = ',';

                                                        }
                                                        option[k] = val;
                                                    });

                                                    if (!attr_arr_all['options'])
                                                        attr_arr_all['options'] = new Array();
                                                    attr_arr_all['options'].push(option);
                                                    //if(!option['checked']) option['checked'] = '';
                                                    var checked = option['checked'] != undefined ? 'checked="checked"' : '';
                                                    attr_arr_all['content'] += '<input type="checkbox" name="' + option['name'] + '" value="' + option['value'] + '"  ' + checked + '/>' + option['value'] + '&nbsp;';

                                                    if (is_new) {
                                                        var arr = new Object();
                                                        arr['name'] = option['name'];
                                                        arr['leipiplugins'] = attr_arr_all['leipiplugins'];
                                                        add_fields[option['name']] = arr;

                                                    }

                                                });
                                                attr_arr_all['content'] += '</span>';

                                                //parse
                                                template = template.replace(plugin, attr_arr_all['content']);
                                                template_parse = template_parse.replace(plugin, '{' + name + '}');
                                                template_parse = template_parse.replace('{|-', '');
                                                template_parse = template_parse.replace('-|}', '');
                                                template_data[pno] = attr_arr_all;
                                                checkboxs++;
                                                
                                                
                                            } else if (name) {
                                                if (tag == 'radios') /*单选组  一个字段*/
                                                {
                                                    plugin = p0;
                                                    plugin = plugin.replace('|-', '');
                                                    plugin = plugin.replace('-|', '');
                                                    attr_arr_all['value'] = '';
                                                    attr_arr_all['content'] = '<span leipiplugins="radios" name="' + attr_arr_all['name'] + '" title="' + attr_arr_all['title'] + '">';
                                                    var dot = '';
                                                    p5.replace(preg_group, function (parse_group) {
                                                        var option = new Object();
                                                        parse_group.replace(preg_attr, function (str0, k, val) {
                                                            if (k == 'value') {
                                                                attr_arr_all['value'] += dot + val;
                                                                dot = ',';
                                                            }
                                                            option[k] = val;
                                                        });
                                                        option['name'] = attr_arr_all['name'];
                                                        if (!attr_arr_all['options'])
                                                            attr_arr_all['options'] = new Array();
                                                        attr_arr_all['options'].push(option);
                                                        //if(!option['checked']) option['checked'] = '';
                                                        var checked = option['checked'] != undefined ? 'checked="checked"' : '';
                                                        attr_arr_all['content'] += '<input type="radio" name="' + attr_arr_all['name'] + '" value="' + option['value'] + '"  ' + checked + '/>' + option['value'] + '&nbsp;';

                                                    });
                                                    attr_arr_all['content'] += '</span>';

                                                } else {
                                                    attr_arr_all['content'] = is_new ? plugin.replace(/leipiNewField/, name) : plugin;
                                                }
                                                //attr_arr_all['itemid'] = fields;
                                                //attr_arr_all['tag'] = tag;
                                                template = template.replace(plugin, attr_arr_all['content']);
                                                template_parse = template_parse.replace(plugin, '{' + name + '}');
                                                template_parse = template_parse.replace('{|-', '');
                                                template_parse = template_parse.replace('-|}', '');
                                                if (is_new) {
                                                    var arr = new Object();
                                                    arr['name'] = name;
                                                    arr['leipiplugins'] = attr_arr_all['leipiplugins'];
                                                    add_fields[arr['name']] = arr;
                                                }
                                                template_data[pno] = attr_arr_all;


                                            }
                                            pno++;
                                        });

                                        var parse_form = new Object({
                                            'fields': fields, //总字段数
                                            'template': template, //完整html
                                            'parse': template_parse, //控件替换为{data_1}的html
                                            'data': template_data, //控件属性
                                            'add_fields': add_fields//新增控件
                                        });

                                        return JSON.stringify(parse_form);
                                    },
                                    /*type  =  save 保存设计 versions 保存版本  close关闭 */
                                    fnCheckForm: function (type) {
                                        if (leipiEditor.queryCommandState('source'))
                                            leipiEditor.execCommand('source');//切换到编辑模式才提交，否则有bug

                                        if (leipiEditor.hasContents()) {
                                            leipiEditor.sync();
                                            /*同步内容*/

                                            // alert("你点击了保存,这里可以异步提交，请自行处理....");
                                            //获取表单设计器里的内容
                                            var type_value = '', form_id = $("#form_id").val(), formeditor = '';
                                            if (typeof type !== 'undefined') {
                                                type_value = type;
                                            }
                                            var fields =$('#fields').val();
                                            formeditor = leipiEditor.getContent();
                                            //解析表单设计器控件
                                            var parse_form = this.parse_form(formeditor, fields);
                                            var url = "{{asset(route('workflow.formDesignSave'))}}";
                                            $.ajax({
                                                type: 'post',
                                                url: url,
                                                // dataType:'',
                                                data: {parse_form: parse_form, type: type_value, form_id: form_id, formeditor: formeditor},
                                                headers: {
                                                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                                                },
                                                success: function (msg) {
                                                    if (msg == 'success') {
                                                        if (String(type) == 'saveClose') {//点击关闭按钮时保存，关闭窗口
                                                            window.close();
                                                        } else {
                                                            location.reload();
                                                            alert('保存成功');
                                                        }
//                                                        location.reload();
                                                    } else if (msg == 'updateError') {
                                                        alert('编辑失败，该流程正在使用！请重新创建新的表单进行使用');
                                                    }else if(msg == 'titleError'){
                                                        alert('保存失败，请填写title的值');
                                                    } else {
                                                        alert('保存失败');
                                                    }
                                                },
                                                error: function (error) {
                                                    if (error.status == 422) {
                                                        alert('非法操作');
                                                    }
                                                }
                                            });

                                            return false;
                                            //         //--------------以下仅参考-----------------------------------------------------------------------------------------------------
                                            //         var type_value = '', formid = 0, fields = $("#fields").val(), formeditor = '';

                                            //         if (typeof type !== 'undefined') {
                                            //             type_value = type;
                                            //         }
                                            //         //获取表单设计器里的内容
                                            //         formeditor = leipiEditor.getContent();
                                            //         //解析表单设计器控件
                                            //         var parse_form = this.parse_form(formeditor, fields);


                                            //         //异步提交数据
                                            //         $.ajax({
                                            //             type: 'POST',
                                            //             url: '/index.php?s=/index/parse.html',
                                            //             //dataType : 'json',
                                            //             data: {'type': type_value, 'formid': formid, 'parse_form': parse_form},
                                            //             success: function (data) {
                                            //                 if (confirm('查看js解析后，提交到服务器的数据，请临时允许弹窗')) {
                                            //                     win_parse = window.open('', '', 'width=800,height=600');
                                            //                     //这里临时查看，所以替换一下，实际情况下不需要替换
                                            //                     data = data.replace(/<\/+textarea/, '&lt;textarea');
                                            //                     win_parse.document.write('<textarea style="width:100%;height:100%">' + data + '</textarea>');
                                            //                     win_parse.focus();
                                            //                 }

                                            //                 /*
                                            //                  if(data.success==1){
                                            //                  alert('保存成功');
                                            //                  $('#submitbtn').button('reset');
                                            //                  }else{
                                            //                  alert('保存失败！');
                                            //                  }*/
                                            //             }
                                            //         });
                                            //         //------------------------------------------------------------------------------------

                                        } else {
                                            alert('表单内容不能为空！');
//                                            $('#submitbtn').button('reset');
                                            return false;
                                        }
                                    },
                                    /*预览表单*/
                                    fnReview: function () {
                                        if (leipiEditor.queryCommandState('source'))
                                            leipiEditor.execCommand('source');
                                        /*切换到编辑模式才提交，否则部分浏览器有bug*/

                                        if (leipiEditor.hasContents()) {
                                            leipiEditor.sync();

                                            /*同步内容*/
                                            // alert("你点击了预览,请自行处理....");
                                            document.saveform.target = "mywin";
                                            window.open('', 'mywin', "menubar=0,toolbar=0,status=0,resizable=1,left=0,top=0,scrollbars=1,width=" + (screen.availWidth - 10) + ",height=" + (screen.availHeight - 50) + "\"");

                                            document.saveform.action = "{{asset(route('workflow.formDesignPreview'))}}";
                                            document.saveform.submit(); //提交表单

                                            return false;
                                            //--------------以下仅参考-------------------------------------------------------------------


                                            /*设计form的target 然后提交至一个新的窗口进行预览*/
                                            document.saveform.target = "mywin";
                                            window.open('', 'mywin', "menubar=0,toolbar=0,status=0,resizable=1,left=0,top=0,scrollbars=1,width=" + (screen.availWidth - 10) + ",height=" + (screen.availHeight - 50) + "\"");

                                            document.saveform.action = "/index.php?s=/index/preview.html";
                                            document.saveform.submit(); //提交表单
                                            //--------------------------------------------------------------------------
                                        } else {
                                            alert('表单内容不能为空！');
                                            return false;
                                        }
                                    }
                                };

        </script>
        <!-- script end -->

        <div style="width:1px;height:1px">
            <script type="text/javascript">
                var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
                document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F1e6fd3a46a5046661159c6bf55aad1cf' type='text/javascript'%3E%3C/script%3E"));
            </script>
        </div>
    </body>
</html>