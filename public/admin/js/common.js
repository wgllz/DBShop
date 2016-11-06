//加法函数
function accAdd(arg1, arg2) {
	var r1, r2, m;
	try {
		r1 = arg1.toString().split(".")[1].length;
	}
	catch (e) {
		r1 = 0;
	}
	try {
		r2 = arg2.toString().split(".")[1].length;
	}
	catch (e) {
		r2 = 0;
	}
	m = Math.pow(10, Math.max(r1, r2));
	
	return (arg1 * m + arg2 * m) / m;
	//return advFormatNumber((arg1 * m + arg2 * m) / m, 2);
} 

//减法函数
function Subtr(arg1, arg2) {
	var r1, r2, m, n;
	try {
		r1 = arg1.toString().split(".")[1].length;
	}
	catch (e) {
		r1 = 0;
	}
	try {
		r2 = arg2.toString().split(".")[1].length;
	}
	catch (e) {
		r2 = 0;
	}
	m = Math.pow(10, Math.max(r1, r2));
     //last modify by deeka
     //动态控制精度长度
	n = (r1 >= r2) ? r1 : r2;
	return ((arg1 * m - arg2 * m) / m).toFixed(n);
	//return advFormatNumber(((arg1 * m - arg2 * m) / m).toFixed(n), 2);
}


//乘法函数
function accMul(arg1, arg2) {
	var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
	try {
		m += s1.split(".")[1].length;
	}
	catch (e) {
	}
	try {
		m += s2.split(".")[1].length;
	}
	catch (e) {
	}
	return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
	//return advFormatNumber(Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m), 2);
} 

//除法函数
function accDiv(arg1, arg2) {
	var t1 = 0, t2 = 0, r1, r2;
	try {
		t1 = arg1.toString().split(".")[1].length;
	}
	catch (e) {
	}
	try {
		t2 = arg2.toString().split(".")[1].length;
	}
	catch (e) {
	}
	with (Math) {
		r1 = Number(arg1.toString().replace(".", ""));
		r2 = Number(arg2.toString().replace(".", ""));
		return (r1 / r2) * pow(10, t2 - t1);
		//return advFormatNumber((r1 / r2) * pow(10, t2 - t1), 2);
	}
} 
/* *
 * 截取小数位数
 */
function advFormatNumber(value, num) // 四舍五入
{
  var a_str = formatNumber(value, num);
  var a_int = parseFloat(a_str);
  if (value.toString().length > a_str.length)
  {
    var b_str = value.toString().substring(a_str.length, a_str.length + 1);
    var b_int = parseFloat(b_str);
    if (b_int < 5)
    {
      return a_str;
    }
    else
    {
      var bonus_str, bonus_int;
      if (num == 0)
      {
        bonus_int = 1;
      }
      else
      {
        bonus_str = "0.";
        for (var i = 1; i < num; i ++ )
        bonus_str += "0";
        bonus_str += "1";
        bonus_int = parseFloat(bonus_str);
      }
      a_str = formatNumber(a_int + bonus_int, num);
    }
  }
  return a_str;
}

function formatNumber(value, num)
{
  var a, b, c, i;
  a = value.toString();
  b = a.indexOf('.');
  c = a.length;
  if (num == 0)
  {
    if (b != - 1)
    {
      a = a.substring(0, b);
    }
  }
  else
  {
    if (b == - 1)
    {
      a = a + ".";
      for (i = 1; i <= num; i ++ )
      {
        a = a + "0";
      }
    }
    else
    {
      a = a.substring(0, b + num + 1);
      for (i = c; i <= b + num; i ++ )
      {
        a = a + "0";
      }
    }
  }
  return a;
}
//全选操作
function selectCheckBox(input_name, select_checkbox_id) {
    var checked_state = $('#'+select_checkbox_id).attr('checked');
	$("input[name='"+input_name+"[]']").each(function() {
		if(checked_state == 'checked') {
            this.checked = true;
        } else {
            this.checked = false;
        }
	});
}
//反向选择
function reverseSelectCheckBox(input_name) {
    $("input[name='"+input_name+"[]']").each(function() {
        if (this.checked == true) {
            this.checked = false;
        }else {
            this.checked = true;
        }
    });
}
//隐藏成功提示
function close_message(mess_id) {
	$(mess_id).html("");
}
//提示信息 show_state:alert-error,alert-success,alert-info,alert-block
function show_message(mess_id,datetime,mess,show_state,time_state) {
	var	message_str = '<div class="alert '+show_state+'">'+mess+' '+datetime+'<a class="close" onclick="close_message(\''+mess_id+'\');">×</a></div>';
	$(mess_id).html(message_str);
	
	if(time_state != 'false') {
		setTimeout(function(){$(mess_id).html("");},5000);
		return true;
	}
	
	return true;
}

/* 地区选择函数 */
function regionInit(divId)
{
    $("#" + divId + " > select").change(regionChange); // select的onchange事件
}

function regionChange()
{
    // 删除后面的select
    $(this).nextAll("select").remove();
    // 计算当前选中到id和拼起来的name
    var selects = $(this).siblings("select").andSelf();
    var id = 0;
    var i;
    var names = new Array();
    for (i = 0; i < selects.length; i++)
    {
        sel = selects[i];
        if (sel.value > 0)
        {
            id = sel.value;
            name = sel.options[sel.selectedIndex].text;
            names.push(name);
        }
    }
    $(".region_ids").val(id);
    $(".region_names").val(names.join(""));
    // ajax请求下级地区
    if (this.value > 0)
    {
        var _self = this;
        var url = SITE_REGION_URL;
        $.post(url, {'region_id':this.value}, function(data){
            if (data)
            {
                if (data.length > 0)
                {
                    $("<select class='db_show_area span8'><option>"+AREA_SELECT_LANG+"</option></select>").change(regionChange).insertAfter(_self);
                    var data  = data;
                    for (i = 0; i < data.length; i++)
                    {
                        $(_self).next("select").append("<option value='" + data[i].region_id + "'>" + data[i].region_name + "</option>");
                    }
                }
            }
        },
        'json');
    }
}

function regionEdit()
{
    $("#show_address_area").show();
    $("#show_region_value").hide();
}
//通用获取列表方法
function dbshop_ajax_list(list_url,show_div_id) {
	$.get(list_url,{show_div_id:show_div_id}, function(html){
		$("#"+show_div_id).html(html);
	}); 
}