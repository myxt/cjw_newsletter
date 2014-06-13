<div class="statistics">
{* Only get here on failure - shouldn't ever happen - well, if I solve the problem of not having a button for inappropriate newsletters *}
{foreach $errors as $error}
	<div class="feedback">
		{switch match=$error}
			{case match="No content"}
				{"No file download statistics possible."|i18n('design/newsletter/statistics/error')}
			{/case}
			{case match="bad_setting"}
				{"Check settings - missing root node id."|i18n('design/newsletter/statistics/error')}
			{/case}
			{case match="no_access"}
				{"Permission Denied."|i18n('design/newsletter/statistics/error')}
			{/case}
			{case match="no_node"}
				{"No valid node."|i18n('design/newsletter/statistics/error')}
			{/case}
			{case match="bad_node"}
				{"Node is not a newletter edition or list."|i18n('design/newsletter/statistics/error')}
			{/case}
			{case}
				{$error} {"Undefined."|i18n('design/newsletter/statistics/error')}
			{/case}
        {/switch}
	</div>
{/foreach}
</div>