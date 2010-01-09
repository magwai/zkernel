<?php

class Magwai_Controller_Crule extends Magwai_Controller_Action {
	function ctlinit() {
		$m = new Site_Model_Crole();
		$tt = $m->fetchPairs();
		if ($tt) $ft = array(
			'title' => 'Роли',
			'type' => 'multiCheckbox',
			'param' => array(
				'multioptions' => $tt
			),
			'm2m' => array(
				'model' => new Site_Model_Crulerole(),
				'self' => 'parentid',
				'foreign' => 'role'
			)
		);
		else $ft = array(
			'active' => false
		);

		$m1 = new Site_Model_Cresource();
		$tt1 = $m1->fetchPairs();
		if ($tt1) $ft1 = array(
			'title' => 'Ресурсы',
			'type' => 'multiCheckbox',
			'param' => array(
				'multioptions' => $tt1
			),
			'm2m' => array(
				'model' => new Site_Model_Cruleresource(),
				'self' => 'parentid',
				'foreign' => 'resource'
			)
		);
		else $ft1 = array(
			'active' => false
		);

		$this->_helper->control()->config->set(array(
			'pre_view' =>
'php_function:$data = $control->config->data;
if (count($data)) {

	$m_r = new Site_Model_Crole();
	$m_rr = new Site_Model_Crulerole();
	foreach ($data as $num => $el) {
		$sel = $m_r->getDefaultAdapter()->select()
			->from(array("r" => $m_r->info("name")), array("r.title"))
			->join(array("rr" => $m_rr->info("name")), "rr.role = r.id")
			->where("rr.parentid = ?", $el->id);
		$res = $m_r->fetchCol($sel);
		$el->role = $res ? implode(", ", $res) : "Все";

		$m_s = new Site_Model_Cresource();
		$m_ss = new Site_Model_Cruleresource();
		$sel = $m_s->getDefaultAdapter()->select()
			->from(array("r" => $m_s->info("name")), array("r.title"))
			->join(array("rr" => $m_ss->info("name")), "rr.resource = r.id")
			->where("rr.parentid = ?", $el->id);
		$res = $m_r->fetchCol($sel);
		$el->resource = $res ? implode(", ", $res) : "Все";
	}
}',
			'field' => array(
				'rule' => array(
					'title' => 'Правило',
					'type' => 'select',
					'param' => array(
						'multioptions' => array(
							'1' => 'Разрешить',
							'0' => 'Запретить'
						)
					),
					'sortable' => true,
					'formatter' => 'function',
					'formatoptions' => 'return Number(data.rule) ? "Разрешить" : "Запретить";'
				),
				'role' => $ft,
				'resource' => $ft1
			)
		));
	}
}