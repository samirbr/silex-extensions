<?php

namespace {{ namespace }}\Controller{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use Silex\Application;

{% if 'new' in actions or 'edit' in actions or 'delete' in actions %}
use Symfony\Component\HttpFoundation\Request;
{%- endif %}

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use {{ namespace }}\Entity\{{ entity }};
{% if 'new' in actions or 'edit' in actions %}
use {{ namespace }}\Form\{{ entity }}Type;
{% endif %}

/**
 * {{ entity }} controller.
 *
{% if 'annotation' == format %}
 * @Route("/{{ route_name_prefix }}")
 * @Method("GET")
{% endif %}
 */
class {{ entity_class }}Controller extends Controller
{

    {%- if 'index' in actions %}
        {%- include 'actions/index.php' %}
    {%- endif %}

    {%- if 'show' in actions %}
        {%- include 'actions/show.php' %}
    {%- endif %}

    {%- if 'new' in actions %}
        {%- include 'actions/new.php' %}
        {%- include 'actions/create.php' %}
    {%- endif %}

    {%- if 'edit' in actions %}
        {%- include 'actions/edit.php' %}
        {%- include 'actions/update.php' %}
    {%- endif %}

    {%- if 'delete' in actions %}
        {%- include 'actions/delete.php' %}
    {%- endif %}

}
