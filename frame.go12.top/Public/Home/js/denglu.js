var jieqiUserId = 0;
var jieqiUserName = '';
var jieqiUserPassword = '';
var jieqiUserGroup = 0;
var jieqiNewMessage = 0;
var jieqiUserVip = 0;
var jieqiUserHonor = '';
var jieqiUserGroupName = '';
var jieqiUserVipName = '';

if(document.cookie.indexOf('jieqiUserInfo') >= 0){
	var jieqiUserInfo = get_cookie_value('jieqiUserInfo');
	start = 0;
	offset = jieqiUserInfo.indexOf(',', start); 
	while(offset > 0){
		tmpval = jieqiUserInfo.substring(start, offset);
		tmpidx = tmpval.indexOf('=');
		if(tmpidx > 0){
           tmpname = tmpval.substring(0, tmpidx);
		   tmpval = tmpval.substring(tmpidx+1, tmpval.length);
		   if(tmpname == 'jieqiUserId') jieqiUserId = tmpval;
		   else if(tmpname == 'jieqiUserName_un') jieqiUserName = tmpval;
		   else if(tmpname == 'jieqiUserPassword') jieqiUserPassword = tmpval;
		   else if(tmpname == 'jieqiUserGroup') jieqiUserGroup = tmpval;
		   else if(tmpname == 'jieqiNewMessage') jieqiNewMessage = tmpval;
		   else if(tmpname == 'jieqiUserVip') jieqiUserVip = tmpval;
		   else if(tmpname == 'jieqiUserHonor_un') jieqiUserHonor = tmpval;
		   else if(tmpname == 'jieqiUserGroupName_un') jieqiUserGroupName = tmpval;
		}
		start = offset+1;
		if(offset < jieqiUserInfo.length){
		  offset = jieqiUserInfo.indexOf(',', start); 
		  if(offset == -1) offset =  jieqiUserInfo.length;
		}else{
          offset = -1;
		}
	}
}

document.write("<dd>");

if(jieqiUserId != 0 && jieqiUserName != '' ){//&& (document.cookie.indexOf('PHPSESSID') != -1 || jieqiUserPassword != '')
	document.write('»¶Ó­Äú ' + jieqiUserName + ' £¬[<a href="/logout.php">×¢Ïú</a>]');
	if(jieqiNewMessage > 0){ document.write('[<a href="/message.php?box=inbox"><span class="red">ÄúÓÐÐÂÏûÏ¢</span></a>]'); }
}
else
{
	document.write('»¶Ó­Äú£¬[<a href="javascript:;" onClick="openDialog(');
	document.write("'");
	document.write('/login.php?jumpurl=&ajax_gets=jieqi_contents');
	document.write("'");
	document.write(', false);">µÇÂ¼</a>]»ò[<a href="javascript:;" onClick="openDialog(');
	document.write("'");
	document.write('/register.php?ajax_gets=jieqi_contents');
	document.write("'");
	document.write(', false);">×¢²á</a>]');
}
document.write("</dd>");

function get_cookie_value(Name) { 
  var search = Name + "=";
¡¡var returnvalue = ""; 
¡¡if (document.cookie.length > 0) { 
¡¡  offset = document.cookie.indexOf(search) 
¡¡¡¡if (offset != -1) { 
¡¡¡¡  offset += search.length 
¡¡¡¡  end = document.cookie.indexOf(";", offset); 
¡¡¡¡  if (end == -1) 
¡¡¡¡  end = document.cookie.length; 
¡¡¡¡  returnvalue=unescape(document.cookie.substring(offset, end));
     }
    }
¡¡    return returnvalue; 
  }