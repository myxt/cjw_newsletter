<div class="statistics">
        <div class="error">

                <h2>Er ging iets mis:</h2>
                <ul>
                        {foreach $errors as $error}
                                <li>{$error|wash()}</li>
                        {/foreach}
                </ul>

        </div>
</div>
