<form method="post" action={'/setup/datatype'|ezurl}>

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Datatype wizard (step 1 of 3)'|i18n( 'design/admin/setup/rad/datatype' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">
<p>
{'Welcome to the wizard for creating a new datatypes. Everything you store in your content objects are called attributes. These attributes are defined as a data types. To be able to customize the storing and validation of these attributes, you can create your own data types.'|i18n( 'design/admin/setup/rad/datatype' )}
</p>
</div>

{* DESIGN: Content END *}</div></div></div>

<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
<input type="hidden" value="basic" name="OperatorStep" />
<input class="button" type="submit" name="" value="{'Restart'|i18n( 'design/admin/setup/rad/datatype' )}" disabled="disabled" />
<input class="button" type="submit" name="DatatypeStepButton" value="{'Next'|i18n( 'design/admin/setup/rad/datatype' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</form>
