
    /**
     * Finds and displays a {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/show", name="{{ route_name_prefix }}_show")
     * @Method("GET")
     * @Template()
{% endif %}
     */
    public function showAction($id, Application $app)
    {
			$entity = $app['db.orm.em']->getRepository('{{ namespace }}\Entity\{{ entity }}')
				->find($id);

			if ( ! $entity) {
					return $app->abort(404, 'Unable to find {{ entity_class }} entity.');
			}

			{% if 'delete' in actions %}
			$deleteForm = $this->createDeleteForm($id);
			{% endif %}
			
			return $app['twig']->render('{{ entity_class }}\show.html.twig', array(
				'entity'      => $entity,
				{% if 'delete' in actions %}
				'delete_form' => $deleteForm->createView()
				{%- endif %}
			));
    }
