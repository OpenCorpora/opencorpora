{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<p>Вы входите на наш сайт в первый раз. Для того, чтобы продолжить работу, вы должны подтвердить свое согласие с лицензией.</p>
<form action="?act=login_openid2" method="post">
<label><input type='checkbox' name='agree' onclick="$('#reg_button').attr('disabled', !$(this).attr('checked'))"/> Я согласен на неотзывную публикацию всех вносимых мной изменений в соответствии с лицензией <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.ru">Creative Commons Attribution/Share-Alike 3.0</a></label><br/>
<button id="reg_button" disabled="disabled">Продолжить</button>
</form>
{/block}
