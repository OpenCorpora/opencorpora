{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Согласие с лицензией</h1>
<p>Вы входите на наш сайт в первый раз. Для того, чтобы продолжить работу, вы должны подтвердить свое согласие с лицензией.</p>
<form action="?act=login_openid2" method="post">
    <label class="checkbox"><input type='checkbox' name='agree' onclick="$('#reg_button').attr('disabled', !$(this).is(':checked'))"/> Я согласен на неотзывную публикацию всех вносимых мной изменений в соответствии с лицензией <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.ru" target="_blank">Creative Commons Attribution/Share-Alike 3.0</a></label>
    <button id="reg_button" disabled="disabled" class="btn btn-primary">Продолжить</button>
</form>
{/block}
