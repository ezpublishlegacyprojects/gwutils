{def $base='WorkflowEvent'
     $publish_id_array=$event.content.publish_id_array
     $unpublish_id_array=$event.content.unpublish_id_array}
<div class="block">
<div class="element">
    {def $possibleClasses=$event.workflow_type.class_attributes}
    <label>{"Publish attributes"|i18n("gwupdatehidden/eventtype/edit")}</label><div class="labelbreak"></div>
    <select name="{$base}_data_updatehidden_publish_attribute_{$event.id}[]" size="10" multiple="multiple">
    {foreach $possibleClasses as $class_attribute}
        <option value="{$class_attribute.id}"{if $publish_id_array|contains($class_attribute.id)} selected="selected"{/if}>{$class_attribute.class.name|wash(xhtml)} / {$class_attribute.class_attribute.name|wash(xhtml)}</option>
    {/foreach}
    </select>
</div>

<div class="element">
    <label>{"Unpublish attributes"|i18n("gwupdatehidden/eventtype/edit")}</label><div class="labelbreak"></div>
    <select name="{$base}_data_updatehidden_unpublish_attribute_{$event.id}[]" size="10" multiple="multiple">
    {foreach $possibleClasses as $class_attribute}
        <option value="{$class_attribute.id}"{if $unpublish_id_array|contains($class_attribute.id)} selected="selected"{/if}>{$class_attribute.class.name|wash(xhtml)} / {$class_attribute.class_attribute.name|wash(xhtml)}</option>
    {/foreach}
    </select>
</div>
<div class="break"></div>
</div>

<div class="block">
<input type="hidden" name="{$base}_data_updatehidden_do_update_{$event.id}" value="1" />
<label><input type="checkbox" name="{$base}_data_updatehidden_modifydate_{$event.id}" value="1" {section show=$event.data_int1}checked="checked"{/section} /> {'Modify publish date'|i18n('gwupdatehidden/eventtype/edit')}</label>
</div>