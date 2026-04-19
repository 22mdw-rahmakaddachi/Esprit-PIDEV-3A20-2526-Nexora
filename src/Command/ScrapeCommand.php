<?php

namespace App\Command;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeCommand extends Command
{
    protected static $defaultName = 'app:scrape';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://books.toscrape.com');

        $html = $response->getContent();

        $crawler = new Crawler($html);

        $crawler->filter('.product_pod h3 a')->each(function (Crawler $node) use ($output) {
            $titre = $node->attr('title');
            $output->writeln($titre);
        });

        return Command::SUCCESS;
    }
}