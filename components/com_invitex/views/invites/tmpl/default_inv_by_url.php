<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_BASE . '/components/com_affiliatetracker/phpqrcode.php');
$invURL = $this->invhelperObj->getinviteURL();

if (strpos($invURL, '?') !== false)
{
	$invURL .= "&method_of_invite=invite_by_url";
}
else
{
	$invURL .= "?method_of_invite=invite_by_url";
}

$invURL = $this->invhelperObj->givShortURL($invURL);

?>

<form>
	<div class="">
		<div class="form-group">
			<span class="invitex_label"><i>*</i><?php echo JText::_('INV_URL_LABLE');?>:</span>
			<!-- <label for="invite_url"><h4><?php echo JText::_('INV_URL_LABLE');?></h4></label> -->
			<input readonly="true" id="invite_url" class="invite_url_show form-control" name="invite_url" value="<?php echo $invURL; ?>" onclick="this.select();">
		</div>
		<div class="form-group" style="margin-top:30px;">
			<span class="invitex_label">邀请二维码:</span>
			<div class="code">
				<img src="<?php echo QRcode::png_object_clean($invURL);?>" alt="" style="width: 100%;height:100%;">
			</div>
			<span class="invitex_label" style="margin-left:70px; margin-right:-5px">分享到:</span>
<!---->
<!--            <div onclick="shareTo('qzone')">-->
<!--                <img src="http://zixuephp.net/static/images/qqzoneshare.png" width="30">-->
<!--            </div>-->
<!--            <div onclick="shareTo('qq')">-->
<!--                <img src="http://zixuephp.net/static/images/qqshare.png" width="32">-->
<!--            </div>-->
<!--            <div onclick="shareTo('sina')">-->
<!--                <img src="http://zixuephp.net/static/images/sinaweiboshare.png" width="36">-->
<!--            </div>-->
<!--            <div onclick="shareTo('wechat')">-->
<!--                <img src="http://zixuephp.net/static/images/wechatshare.png" width="32">-->
<!--            </div>-->


            <div class="share" onclick="shareTo('sina','<?php echo $invURL;?>')">
				<img src="components/com_invitex/images/sina.png" alt="" style="width:26px">
				<p>微博</p>
			</div>
			<div class="share" onclick="shareTo('wechat','<?php echo $invURL;?>')">
				<img src="components/com_invitex/images/WeChat.png" alt="" style="width:26px">
				<p>微信好友</p>
			</div>
			<div class="share" onclick="shareTo('qzone','<?php echo $invURL;?>')">
				<img src="components/com_invitex/images/qq.png" alt="" style="width:26px">
				<p>QQ空间</p>
			</div>
			<div class="share" onclick="shareTo('qq','<?php echo $invURL;?>')">
				<img src="components/com_invitex/images/circle of friends.png" alt="" style="width:26px">
				<p>朋友圈</p>
			</div>
		</div>
	</div>
</form>
<script>
    function shareTo(stype,share_href){
        var ftit = '';
        var flink = '';
        var lk = '';
        //获取文章标题
        ftit = document.title;
        //获取网页中内容的第一张图片地址作为分享图
        flink = document.images[0].src;
        if(typeof flink == 'undefined'){
            flink='';
        }
        //当内容中没有图片时，设置分享图片为网站logo
        if(flink == ''){
            lk = 'http://'+window.location.host+'/static/images/logo.png';
        }
        //如果是上传的图片则进行绝对路径拼接
        if(flink.indexOf('/uploads/') != -1) {
            lk = 'http://'+window.location.host+flink;
        }
        //百度编辑器自带图片获取
        if(flink.indexOf('ueditor') != -1){
            lk = flink;
        }
        //qq空间接口的传参
        if(stype=='qzone'){
            window.open('https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+share_href+'?sharesource=qzone&title='+ftit+'&pics='+lk+'&summary='+document.querySelector('meta[name="description"]').getAttribute('content'));
        }
        //新浪微博接口的传参
        if(stype=='sina'){
            window.open('http://service.weibo.com/share/share.php?url='+share_href+'?sharesource=weibo&title='+ftit+'&pic='+lk+'&appkey=2706825840');
        }
        //qq好友接口的传参
        if(stype == 'qq'){
            window.open('http://connect.qq.com/widget/shareqq/index.html?url='+share_href+'?sharesource=qzone&title='+ftit+'&pics='+lk+'&summary='+document.querySelector('meta[name="description"]').getAttribute('content')+'&desc=php自学网，一个web开发交流的网站');
        }
        //生成二维码给微信扫描分享，php生成，也可以用jquery.qrcode.js插件实现二维码生成
        if(stype == 'wechat'){
            window.open('http://zixuephp.net/inc/qrcode_img.php?url='+share_href);
        }
    }
</script>
