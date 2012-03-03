<?php

include('miniOrm.php');

/*
$currencies = Db::inst()->getArray('*', 'ps_currency');
$currency = Db::inst()->getRow('*', 'ps_currency', array('iso_code="EUR"', 'id_currency=1'));
$currency_iso_code = Db::inst()->getValue('iso_code', 'ps_currency', array('iso_code="EUR"', 'id_currency=1'));
$iso_codes = Db::inst()->getValueArray('iso_code', 'ps_currency');
$id_currency = Db::inst()->insert('ps_currency', array('iso_code'=> 'EUR', 'id_currency'=> 1));
$count_currency Db::inst()->count('ps_currency');
*/

/*
$article = new Obj('spip_articles', 1);
$article->v['titre'] = 'Article modifié';
$article->update();
$article->delete();

$new_article = new Obj('spip_articles');
$new_article->v['titre'] = 'Nouvel article bis';
$new_article->v['id_rubrique'] = 1;
$new_article->add();
*/