<?php

namespace Nng\Nnrestapi\Mvc\View;

use TYPO3\CMS\Extbase\Mvc\View\JsonView as ExtbaseJsonView;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class JsonView extends ExtbaseJsonView
{

	/**
	 * @var array
	 */
	protected $configuration = [
		'data' => [
			'_descend' => [
				'_only' => ['pid', 'additionalDates'],
				'falMedia' => [
					'_descendAll' => [
						'_only' => ['uid']
					]
				],
				'category' => [
					'_descendAll' => [
						'_only' => ['uid']
					]
				]
			],
		],
	];

	/**
	 * Always transforming ObjectStorages to Arrays for the JSON view
	 *
	 * @param mixed $value
	 * @param array $configuration
	 * @return array
	 */
	protected function transformValue($value, array $configuration)
	{
		/*
		if ($value instanceof CalendarEntry) {
			$value = \nn\t3::Convert($value)->toArray(6);
		}
		*/
		return parent::transformValue($value, $configuration);
	}
}