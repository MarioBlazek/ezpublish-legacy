{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{*?template charset=latin1?*}
{include uri='design:setup/setup_header.tpl' setup=$setup}

{section show=$test.result|eq(1)}
  <p>{"No finetuning is required on your system, you can continue by clicking the"|i18n("design/standard/setup/init")} <i>{"Next"|i18n("design/standard/setup/init")}</i> {"button."|i18n("design/standard/setup/init")}</p>

  <form method="post" action="{$script}">
    <div class="buttonblock">
      <input type="hidden" name="ChangeStepAction" value="" />
      <input class="defaultbutton" type="submit" name="StepButton_4" value="{'Next'|i18n('design/standard/setup/init')} >>" />
    </div>
    {include uri='design:setup/persistence.tpl'}
  </form>

{section-else}

  <p>
  {"The system check found some issues that, when resolved, may give improved performance or more features. Please have a look through the results below for more information on what might be done. Each issue will give you instructions on how to do the finetuning."|i18n("design/standard/setup/init")}
  </p>
  <p>
   {"After you have fixed the problems click the"|i18n("design/standard/setup/init")} <i>{"Rerun System Check"|i18n("design/standard/setup/init")}</i> {"button to re-run the system checking. This is recommended after system changes to check for critical failures. You can also click the"|i18n("design/standard/setup/init")} <i>{"Check Again"|i18n("design/standard/setup/init")}</i> {"button to rerun the finetuning checks. However if you wish you can skip straight to the next step by clicking the"|i18n("design/standard/setup/init")} <i>{"Next"|i18n("design/standard/setup/init")}</i> {"button."|i18n("design/standard/setup/init")}
  </p>

  <h1>{"Issues"|i18n("design/standard/setup/init")}</h1>

  <form method="post" action="{$script}">

  <table width="100%" border="0" cellpadding="0" cellspacing="0">
  {section name=Result loop=$test.results}
  {section-exclude match=$:item[0]|ne(2)}
  <tr>
    <td>{include uri=concat('design:setup/tests/',$:item[1],'_error.tpl') test_result=$:item result_number=$:number}</td>
  </tr>

  {delimiter}
  <tr><td>&nbsp;</td></tr>
  {/delimiter}

  {/section}
  </table>

    <div class="buttonblock">
      <input type="hidden" name="ChangeStepAction" value="" />
      <input class="defaultbutton" type="submit" name="StepButton_4" value="{'Next'|i18n('design/standard/setup/init')} >>" />
      <input class="button" type="submit" name="StepButton_3" value="{'Check Again'|i18n('design/standard/setup/init')}" />
      <input class="button" type="submit" name="StepButton_2" value="< {'Rerun System Check'|i18n('design/standard/setup/init')}" />
    </div>
    {include uri='design:setup/persistence.tpl'}
  </form>

{/section}
