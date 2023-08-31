<!--<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ l('CTA Producto') }}</label>
                <input type="text" name="cta_producto" value="{{ ctaProductoValue }}" />
            <p>Valor de ctaProductoValue: {{ ctaProductoValue }}</p>
        </div>
    </div>
</div>-->
<div class="col-md-12">
    <div class="row">
        <fieldset class="col-md-4 form-group">
            <label class="form-control-label">{l s='CTA Producto' mod='wktestadminproductextra'}</label>
            <input type="text" id="test_input" class="form-control" name="cta_producto" value="{l ctaProductoValue }"></input>
            <small class="form-text text-muted"><em>{l s='This is test input field' mod='wktestadminproductextra'}</em></small>
        </fieldset>
    </div>
</div>