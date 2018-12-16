<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	public function renderDefault()
	{

	}

	protected function createComponentFileUploadForm()
	{
		$form = new Form;

		$form->addUpload('file', 'Soubor:')->setRequired();
		$form->addSubmit('send', 'Nahrát');

		$form->onSuccess[] = [$this, 'fileUploaded'];

		return $form;
	}

	public function fileUploaded(Form $form, \StdClass $values)
	{
		$calendar = new \Eluceo\iCal\Component\Calendar('www.example.com');
		$uploadedFile = $values->file;

		$file = file_get_contents($uploadedFile);

		$data = str_getcsv($file);

		for ($i = 10; $i < count($data) - 1; $i = $i + 10)
		{
			if (!empty($data[$i + 3]))
			{
				$dateSource = explode("\"", $data[$i]);
				$date = substr($dateSource[1], 0, 10);

				$timeSource = explode("|", $data[$i + 3]);

				foreach ($timeSource as $times)
				{
					$event = new \Eluceo\iCal\Component\Event();

					$startEndTimes = explode("-", $times);
					$startTime = $startEndTimes[0];
					$endTime = $startEndTimes[1];

					$startStamp = strtotime($date . $startTime);
					$endStamp = strtotime($date . $endTime);

					if ($startStamp > $endStamp)
					{
						$endStamp = $endStamp + 24 * 60 * 60;
					}

					$start = date('d.m.Y H:i', $startStamp);
					$end = date('d.m.Y H:i', $endStamp);

					$event->setDtStart(new \DateTime($start))->setDtEnd(new \DateTime($end))->setSummary('Směna');

					$calendar->addComponent($event);
				}
			}
		}

		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename="cal.ics"');

		echo $calendar->render();
		exit();
	}
}
