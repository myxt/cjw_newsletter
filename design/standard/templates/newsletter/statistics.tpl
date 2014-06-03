{* Only get here on failure - shouldn't ever happen *}
{foreach $errors as $error}
	<div class="feedback">
        {switch match=$error}
				{case match="no_node"}
					{"Cookies must be enabled to participate."|i18n('design/exam')}
				{/case}
				{case}
					{$error} {"Undefined."|i18n('design/error')}
				{/case}
        {/switch}
	</div>
{/foreach}
