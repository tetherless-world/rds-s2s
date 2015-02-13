<?php

include_once "utils.php";
include_once "config.php";

class RDS_S2SConfig extends S2SConfig
{
	// TODO CHANGE TO GET VIVO_URL_PREFIX FROM ENV_VARIABLE
	public $VIVO_URL_PREFIX = "http://data.tw.rpi.edu/rds-vivo/individual";

	// TODO GET namespaces array from config file
	private $namespaces = array(
		'foaf'	=> "http://xmlns.com/foaf/0.1/",
		'rdfs'	=> "http://www.w3.org/2000/01/rdf-schema#",
		'time'	=> "http://www.w3.org/2006/time#",
		'vivo'	=> "http://vivoweb.org/ontology/core#",
		'vitro'	=> "http://vitro.mannlib.cornell.edu/ns/vitro/0.7#",
		'bibo'	=> "http://purl.org/ontology/bibo/",
		'xsd'	=> "http://www.w3.org/2001/XMLSchema#",
		'skos'	=> "http://www.w3.org/2004/02/skos/core#",
		'owl'	=> "http://www.w3.org/2002/07/owl#",
		'dct'	=> "http://purl.org/dc/terms/",
		'dc'	=> "http://purl.org/dc/elements/1.1/",
		'obo'	=> "http://purl.obolibrary.org/obo/",
		'dcat'	=> "http://www.w3.org/ns/dcat#",
        'rds'   => "http://purl.org/twc/ns/rds#",
        'fn'    => "http://jena.hpl.hp.com/ARQ/function#"
	);

	// TODO change to get ENDPOINT FROM ENV_VARIABLE
	public function getEndpoint() {
		return "http://data.tw.rpi.edu/info/admin/sparqlquery";
	}

	public function getNamespaces() {
		return $this->namespaces;
	}
	
	public function sparqlSelect($query) {
	
		$options = array(
			CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 60
		);
				
		$encoded_query = 'query=' . urlencode($query) . '&resultFormat=RS_XML';
		return execSelect($this->getEndpoint(), $encoded_query, $options);
	}

    /*
	private function getAuthorsByDataset($dataset) {
				
		$query = $this->getPrefixes();
		$query .= "SELECT DISTINCT ?uri ?name WHERE { ";
		$query .= "?authorship vivo:relates <$dataset> . ";
		$query .= "?authorship a vivo:Authorship . ";
		$query .= "?authorship vivo:relates ?author . ";
		$query .= "?author a foaf:Agent . ";
		$query .= "?author rdfs:label ?l . ";
		$query .= "BIND(str(?author) AS ?uri ) . ";
		$query .= "BIND(str(?l) AS ?name) } ";
				
		return $this->sparqlSelect($query);
	}
    */
	
	private function getContributorsByDataset($dataset) {
	
		$query = $this->getPrefixes();
		$query .= "SELECT DISTINCT ?uri ?name WHERE { ";
		$query .= "<$dataset> dct:contributor ?contributor . ";
		$query .= "?contributor a foaf:Agent . ";
		$query .= "?contributor rdfs:label ?label . ";
		$query .= "BIND(str(?contributor) AS ?uri) . ";
		$query .= "BIND(str(?label) AS ?name) } ";
				
		return $this->sparqlSelect($query);
	}

    private function getLeadResearcherByDataset($dataset) {

        $query = $this->getPrefixes();
        $query .= "SELECT DISTINCT ?uri ?name WHERE { ";
        $query .= "<$dataset> rds:leadResearcher ?leadResearcher . ";
        $query .= "?leadResearcher a foaf:Agent . ";
        $query .= "?leadResearcher rdfs:label ?l . ";
        $query .= "BIND(str(?leadResearcher) AS ?uri ) . ";
        $query .= "BIND(str(?l) AS ?name) } ";

        return $this->sparqlSelect($query);
    }
	
	private function getCatalogsByDataset($dataset) {
		
		$query = $this->getPrefixes();
		$query .= "SELECT DISTINCT ?uri ?name WHERE { ";
		$query .= "?catalog dcat:dataset <$dataset> . ";
		$query .= "?catalog a dcat:Catalog . ";
		$query .= "?catalog rdfs:label ?title . ";
		$query .= "BIND(str(?catalog) AS ?uri) . ";
		$query .= "BIND(str(?title) AS ?name) }";
		
		return $this->sparqlSelect($query);
	}

    // fn:lower-case(string)

	private function getKeywordsByDataset($dataset) {
	
		$query = $this->getPrefixes();
		$query .= "SELECT DISTINCT ?keyword WHERE { ";
		$query .= "<$dataset> vivo:freetextKeyword ?k . ";
		$query .= "BIND(fn:lower-case(str(?k)) AS ?keyword) } ";
		
		return $this->sparqlSelect($query);
	}
	
	// override
	public function getSearchResultCount(array $constraints) {
		
		$query = $this->getSelectQuery("count", $constraints);
		$results = $this->sparqlSelect($query);
		$result = $results[0];
		return $result['count'];
	}

    private function updateArrayOfHrefs(array &$markup, $uri, $label) {
        $leadResearcher_vivo_url = $this->VIVO_URL_PREFIX . "?uri=" . urlencode($uri);
        array_push($markup, "<a target='_blank_' href=\"" . $leadResearcher_vivo_url . "\">" . $label . "</a>");
    }

    private function createSimpleSpan($label, $value) {
        if(isset($value) && isset($label)) {
            return "<br /><span>" . $label . ": " . $value . "</span>";
        } else {
            return "";
        }
    }

    private function createLinkedSpan($label, $uri, $value) {

        if(isset($value) && isset($uri)) {
            return "<br /><span>" . $label . ": <a target='_blank_' href=\"" . $uri . "\">" . $value . "</a></span>";
        } else {
            return "";
        }
    }

    private function createSpanWithListOfHrefs(array $list, $label, $uri_key, $label_key) {

        if (count($list) > 0) {
            $html = "";
            $html .= "<br /><span>" . $label . ": ";
            $markup = array();
            foreach ($list as $i => $item) {
                $this->updateArrayOfHrefs($markup, $item[$uri_key], $item[$label_key]);
            }
            $html .= implode('; ', $markup);
            $html .= "</span>";
            return $html;
        } else {
            return "";
        }
    }

    private function createSpanWithSimpleList(array $list, $label, $label_key) {

        if(count($list) > 0) {
            $html = "";
            $html .= "<br /><span>". $label .": ";
            $markup = array();
            foreach ($list as $i => $item) {
                array_push($markup, $item[$label_key]);
            }
            $html .= implode('; ', $markup);
            $html .= "</span>";
            return $html;
        } else {
            return "";
        }
    }

	// override	
	public function getSearchResultOutput(array $result) {
							
		$dataset = $result['dataset'];
		$html = "<div class='result-list-item'>";
	
		// title
		$dataset_vivo_url = $this->VIVO_URL_PREFIX . "?uri=" . urlencode($dataset);
		$html .= "<span class='title'><a target='_blank_' href=\"" . $dataset_vivo_url . "\">" . $result['label'] . "</a></span>";	
	
		// description
		if(isset($result['description'])) {
			$description = $result['description'];
			$summary_end = strpos($description, '.') + 1;
			$description_summary = substr($description, 0, $summary_end);
            $html .= $this->createSimpleSpan("Description", $description_summary);
		}

		// handle
        $html .= $this->createSimpleSpan("Handle", $result['id']);
	
		// date issued
        $html .= $this->createSimpleSpan("Date Issued", $result['issued']);

        $leadResearchers = $this->getLeadResearcherByDataset($dataset);
        $html .= $this->createSpanWithListOfHrefs($leadResearchers, "Lead Researcher", 'uri', 'name');
		
		// contributors
		$contributors = $this->getContributorsByDataset($dataset);
        $html .= $this->createSpanWithListOfHrefs($contributors, "Contributors", 'uri', 'name');
		
		// dataset catalogs
		$catalogs = $this->getCatalogsByDataset($dataset);
        $html .= $this->createSpanWithListOfHrefs($catalogs, "Catalogs", 'uri', 'name');

		// keywords
		$keywords = $this->getKeywordsByDataset($dataset);
		$html .= $this->createSpanWithSimpleList($keywords, "Keywords", 'keyword');
		
		// landing page
        $html .= $this->createLinkedSpan("", $result['landingPage'], $result['landingPage']);
	
		$html .= "</div>";				
		return $html;
	}
	
	public function getQueryHeader($type) {
	
		$header = "";
		switch($type) {
			case "datasets":
				$header .= "?dataset ?id ?label ?description ?issued ?landingPage";
				break;
			case "count":
				$header .= "(count(DISTINCT ?dataset) AS ?count)";
				break;
			default:
				$header .= "?id ?label (COUNT(DISTINCT ?dataset) AS ?count)";
				break;
		}
		return $header;
	}
	
	public function getQueryFooter($type, $limit=null, $offset=0, $sort=null) {
	
		$footer = "";
		switch($type) {
			case "datasets":
				$footer .= " LIMIT $limit OFFSET $offset";
				break;
			case "count":
				break;
			default:
				$footer .= " GROUP BY ?label ?id";
				break;
		}
		return $footer;
	}
	
	public function getQueryBody($type) {
		
		$body = "";
		switch($type) {
				
			case "contributors":
				$body .= "?dataset a dcat:Dataset . ";
				$body .= "?dataset dct:contributor ?agent . ";
				$body .= "?agent a foaf:Agent . ";
				$body .= "?agent rdfs:label ?l . ";
				$body .= "BIND(str(?agent) AS ?id) . ";
				$body .= "BIND(str(?l) AS ?label) . ";
				break;

            case "leadResearchers":
                $body .= "?dataset a dcat:Dataset . ";
                $body .= "?dataset rds:leadResearcher ?leadResearcher . ";
                $body .= "?leadResearcher a foaf:Agent . ";
                $body .= "?leadResearcher rdfs:label ?l . ";
                $body .= "BIND(str(?leadResearcher) AS ?id) . ";
                $body .= "BIND(str(?l) AS ?label) . ";
                break;

			case "catalogs":
				$body .= "?dataset a dcat:Dataset . ";
				$body .= "?catalog dcat:dataset ?dataset . ";
				$body .= "?catalog a dcat:Catalog . ";
				$body .= "{ ?catalog rdfs:label ?title } UNION { ?catalog dct:title ?title } UNION { ?catalog dc:title ?title } . ";
				$body .= "BIND(str(?title) AS ?label) . ";
				$body .= "BIND(str(?catalog) AS ?id) . ";
				break;
				
			case "keywords":
				$body .= "?dataset a dcat:Dataset . ";
				$body .= "{ ?dataset vivo:freetextKeyword ?id } UNION { ?dataset dcat:keyword ?id } . ";
				$body .= "BIND(str(?id) AS ?label) . ";
				break;
				
			case "count":
				$body .= "?dataset a dcat:Dataset . ?dataset rdfs:label ?label .";
				break;
				
			case "datasets":
				$body .= "?dataset a dcat:Dataset . ";
				$body .= "{ ?dataset dc:title ?l } UNION { ?dataset dct:title ?l } UNION { ?dataset rdfs:label ?l } . ";
				$body .= "OPTIONAL { ?dataset dc:identifier ?id . } ";
				$body .= "OPTIONAL { ?dataset dct:issued ?issued_date . } ";
				$body .= "OPTIONAL { ?dataset dcat:landingPage ?lp . } ";
				$body .= "OPTIONAL { { ?dataset dc:description ?description } UNION { ?dataset dct:description ?description } UNION { ?dataset vivo:description ?description } . } ";
				$body .= "BIND(str(?l) AS ?label) . ";
				$body .= "BIND(str(?issued_date) AS ?issued) . ";
				$body .= "BIND(str(?lp) AS ?landingPage) . ";
				break;
		}
				
		return $body;
	}
	
	public function getQueryConstraint($constraint_type, $constraint_value) {
		
		$body = "";
		switch($constraint_type) {
			case "catalogs":
				$body .= "{ <$constraint_value> dcat:dataset ?dataset }";
				break;
			case "contributors":
				$body .= "{ ?dataset dct:contributor <$constraint_value> }";
				break;
			case "keywords":
				$body .= "{ ?dataset vivo:freetextKeyword \"$constraint_value\"^^xsd:string } UNION { ?dataset vivo:freetextKeyword \"$constraint_value\" }";
				break;
            case "leadResearchers":
                $body .= "{ ?dataset rds:leadResearcher <$constraint_value> }";
                break;
			default:
				break;
		}
		return $body;
	}
	
	private function addContextLinks(&$results, $type) {
		
		if ($type == 'contributors' || $type == 'catalogs' || $type == 'leadResearchers') {
			foreach ( $results as $i => $result ) {
				$results[$i]['context'] = $this->VIVO_URL_PREFIX . "?uri=" . urlencode($result['id']); 
			}
		}
	}
	
	public function getOutput(array $results, $type, array $constraints, $limit=0, $offset=0) {
				
		if($type == "datasets") {
			$count = $this->getSearchResultCount($constraints);						
			return $this->getSearchResultsOutput($results, $limit, $offset, $count);
		} elseif($type == "keywords") {

            foreach ($results as $i => $result ) {
                $results[$i]['keyword'] = strtolower($result['keyword']);
                $results[$i]['id'] = strtolower($result['id']);
            }
            usort($array, function($a, $b){ return strcmp($a["keyword"], $b["keyword"]); });

            $this->addContextLinks($results, $type);
            return $this->getFacetOutput($results);
        } else {
			$this->addContextLinks($results, $type);
			return $this->getFacetOutput($results);
		}
	}
}