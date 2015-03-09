
    /**
     * Displays a form to edit an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/edit", name="{{ route_name_prefix }}_edit")
     * @Method("GET")
     * @Template()
{% endif %}
     */
    public function editAction($id, Application $app)
    {
			$entity = $app['db.orm.em']->getRepository('{{ namespace }}\Entity\{{ entity }}')
				->find($id);

			if ( ! $entity) {
					return $app->abort(404, 'Unable to find {{ entity_class }} entity.');
			}

			$editForm = $app['form.factory']->create(new {{ entity_class }}Type(), $entity, array());
			$deleteForm = $this->createDeleteForm($id);

			return $app['twig']->render('{{ entity_class }}\edit.html.twig', array(
					'entity'      => $entity,
					'edit_form'   => $editForm->createView(),
					'delete_form' => $deleteForm->createView(),
			));
    }
