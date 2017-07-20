<?php

namespace Larastic\Client;

/**
 * Class QueryBuilder
 * @package Larastic\Client
 */
trait QueryBuilder
{
    /**
     * @var array
     */
    protected $query = [];

    /**
     * @return $this
     */
    public function query()
    {
        return $this;
    }

    /**
     * Get query
     *
     * @return array
     */
    public function build()
    {
        $query = $this->query;
        $this->query = [];

        return $query;
    }

    /**
     * Search by keyword
     *
     * @param $keyword
     * @param array $fields
     * @param string $minimum_match
     * @param string $operator
     *
     * @return $this
     */
    public function match($keyword, array $fields, $minimum_match = '30%', $operator = 'and')
    {
        $this->query['filtered']['query']['bool']['should']['multi_match'] = [
            'query' => $keyword,
            'type' => 'cross_fields',
            'fields' => $fields,
            'minimum_should_match' => $minimum_match,
            'tie_breaker' => 0.3,
            'operator' => $operator
        ];

        return $this;
    }

    /**
     * Filter
     *
     * @param $field
     * @param $value
     * @param string $term
     *
     * @return $this
     */
    public function filter($field, $value, $term = 'term')
    {
        if ($term == 'terms' && ! is_array($value)) {
            if (strpos($value, ',') !== false) {
                $value = explode(',', $value);
            } else {
                $value = [$value];
            }
        }

        $this->query['filtered']['filter']['bool']['must'][] = [
            $term => [$field => $value]
        ];

        return $this;
    }

    /**
     * Range
     *
     * @param $field
     * @param null $max
     * @param null $min
     *
     * @return $this
     */
    public function range($field, $min = null, $max = null)
    {
        $field_value = [];

        if ($min !== null) {
            $field_value['gte'] = $min;
        }

        if ($max !== null) {
            $field_value['lte'] = $max;
        }

        if (! empty($field)) {
            $this->query['filtered']['filter']['bool']['must'][] = [
                'range' => [$field => $field_value]
            ];
        }

        return $this;
    }

    /**
     * Filter by nested object
     *
     * @param $path
     * @param $field
     * @param $value
     * @param string $term
     *
     * @return $this
     */
    public function byNested($path, $field, $value, $term = 'term')
    {
        $this->query['filtered']['filter']['bool']['must'][] = [
            'nested' => [
                'path' => $path,
                'query' => [
                    'bool' => [
                        'must' => [
                            $term => [$path.'.'.$field => $value]
                        ]
                    ]
                ]
            ]
        ];

        return $this;
    }

    /**
     * Search by distance
     *
     * @param $lat
     * @param $lon
     * @param $distance
     */
    public function byDistance($lat, $lon, $distance)
    {
        $this->query['filtered']['query']['bool']['must']['geo_distance'] = [
            'distance' => $distance,
            'map' => [
                'lat' => $lat,
                'lon' => $lon
            ]
        ];
    }

    /**
     * @param array $es
     * @param int $page
     * @param int $limit
     * @param array $source_fields
     * @param array $sort
     * @param null $min_score
     * @return array
     */
    public function getResult(array $es = [], $page = 1, $limit = 15, array $source_fields = [], array $sort = [], $min_score = null)
    {
        $params = [
            'index' => $es['index'],
            'type' => $es['type'],
            'body' => []
        ];

        if ($min_score) {
            $params['body']['min_score'] = $min_score;
        }

        if (! empty($this->query)) {
            $params['body']['query'] = $this->query;
        }

        if (! empty($source_fields)) {
            $params['body']['_source']['include'] = $source_fields;
        }

        if (is_numeric($limit)) {
            $params['size'] = $limit;
        }

        if (is_numeric($page)) {
            $params['from'] = ($page -1)*$limit;
        }

        if (! empty($sort)) {
            $params['body']['sort'] = $sort;
        }

        $result = $this->search($params);

        $total = $result['hits']['total'];
        $result = $result['hits']['hits'];
        $result = collect($result);

        return [
            'data' => $result->pluck('_source'),
            'total' => $total,
        ];
    }
}