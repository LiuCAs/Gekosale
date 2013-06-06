{% extends "layoutbox.tpl" %}
{% block content %}
<h2 class="category-title">{{ producer.name }}</h2>
{% if producer.photo != '' or producer.description != '' %}
<div class="category-description">
	{% if producer.photo != '' %}
	<img style="float: right;right: 0;position: absolute;" src="{{ producer.photo }}" alt="{{ producer.name }}" title="{{ producer.name }}" />
	{% endif %}
	{% if producer.description != '' %}
	<div class="caption">
	{{ producer.description }}
	</div>
	{% endif %}
</div>
{% endif %}
{% if dataset.rows|length > 0 %}
<div class="category-options">
	<form class="form-horizontal">
    	<fieldset>
        	<div class="control-group">
            	<label class="control-label" for="sort">{% trans %}TXT_SORT_RESULTS_SHORTS{% endtrans %}:</label>
                <div class="controls">
                	<select id="sort" class="input-medium" onchange="location.href=this.value">
                    {% for sort in sorting %} 
	    				<option value="{{ sort.link }}" {% if sort.active %}selected{% endif %}>{{ sort.label }}</option> 
	    			{% endfor %}
                    </select>
                </div>
            </div>
		</fieldset>
	</form>
    <div class="category-view">
    	<span>{% trans %}TXT_LAYER{% endtrans %}:</span>
    	{% for switch in viewSwitcher %} 
        <a href="{{ switch.link }}#sort" class="{% if switch.type == 1 %}list{% else%}box{% endif %} {% if switch.active == 1 %}active{% endif %}"></a>
        {% endfor %}
	</div>
    <div class="clearfix"></div>
</div>
{% include 'pagination.tpl' %} 
{% include 'products.tpl' %}
{% include 'pagination.tpl' %}
{% else %}
<div class="alert alert-block alert-error">
{% trans %}ERR_EMPTY_PRODUCT_LIST{% endtrans %}
</div>
{% endif %}
{% endblock %}
