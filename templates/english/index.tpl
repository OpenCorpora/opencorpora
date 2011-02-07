{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h1>Open Corpora</h1>
<p>Hi!</p>
<p>This is the website of the OpenCorpora project. Our goal is to create an annotated (morphologically, syntactically and semantically) corpus of texts in Russian which will be fully accessible to researchers, the annotation being crowd-sourced.</p>
<p>We started in 2009, the development is under way. You may follow our progress <a href="http://opencorpora.googlecode.com">here</a> (yes, the project is opensource).</p>
<h2>How can I help?</h2>
<p>If you:</p>
<ul>
<li>are interested in computational linguistics and want to participate in a real project;</li>
<li>have any programming skills;</li>
<li>know nothing of linguistics or programming, but are curious about what we do</li>
</ul>
<p>&ndash; write us: <b>{mailto address=opencorpora@opencorpora.org encode=javascript}</b></p>
{* Admin options *}
{if $is_admin == 1}
    <a href='{$web_prefix}/books.php'>Sources editor</a><br/>
    <a href='{$web_prefix}/dict.php'>Dictionary editor</a><br/><br/>
    <a href='{$web_prefix}/add.php'>Add text</a><br/>
    <br/>
{/if}
<a href='?rand'>Random sentence</a><br/>
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
