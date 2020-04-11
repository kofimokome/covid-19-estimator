<?php

//$data = json_decode('{"region":{"name":"Africa","avgAge":19.7,"avgDailyIncomeInUSD":5,"avgDailyIncomePopulation":0.71},"periodType":"days","timeToElapse":58,"reportedCases":674,"population":66622705,"totalHospitalBeds":1380614}', true);
//echo print_r(covid19ImpactEstimator($data));


function covid19ImpactEstimator($data)
{
    $return = ['data' => $data, 'impact' => getImpact($data), 'severeImpact' => getSevereImpact($data)];
    return $return;
}

function getImpact($data)
{
    // challenge 1
    $impact = [];
    $impact['currentlyInfected'] = $data['reportedCases'] * 10;
    $impact = array_merge($impact, getInfectionsByRequestedTime($data['periodType'], $data['timeToElapse'], $impact['currentlyInfected'], $data['region']['avgDailyIncomeInUSD'], $data['region']['avgDailyIncomePopulation']));

    // challenge 2
    $impact['severeCasesByRequestedTime'] = 0.15 * $impact['infectionsByRequestedTime'];
    $impact['hospitalBedsByRequestedTime'] = bcdiv((0.35 * $data['totalHospitalBeds']) - $impact['severeCasesByRequestedTime'], 1, 0);

    // challenge 3
    $impact['casesForICUByRequestedTime'] = 0.05 * $impact['infectionsByRequestedTime'];
    $impact['casesForVentilatorsByRequestedTime'] = bcdiv(0.02 * $impact['infectionsByRequestedTime'], 1, 0);

    return $impact;
}

function getSevereImpact($data)
{
    // challenge 1
    $severeImpact = [];
    $severeImpact['currentlyInfected'] = $data['reportedCases'] * 50;
    $severeImpact = array_merge($severeImpact, getInfectionsByRequestedTime($data['periodType'], $data['timeToElapse'], $severeImpact['currentlyInfected'], $data['region']['avgDailyIncomeInUSD'], $data['region']['avgDailyIncomePopulation']));

    // challenge 2
    $severeImpact['severeCasesByRequestedTime'] = 0.15 * $severeImpact['infectionsByRequestedTime'];
    $severeImpact['hospitalBedsByRequestedTime'] = bcdiv((0.35 * $data['totalHospitalBeds']) - $severeImpact['severeCasesByRequestedTime'], 1, 0);

    //challenge 3
    $severeImpact['casesForICUByRequestedTime'] = 0.05 * $severeImpact['infectionsByRequestedTime'];
    $severeImpact['casesForVentilatorsByRequestedTime'] = bcdiv(0.02 * $severeImpact['infectionsByRequestedTime'], 1, 0);

    return $severeImpact;
}

function getInfectionsByRequestedTime($type, $duration, $currentlyInfected, $avgDailyIncomeInUSD, $avgDailyIncomePopulation)
{
    $days = $duration; // assume the default type is days
    switch ($type) {
        case 'weeks':
            $days = $duration * 7;
            break;
        case 'months':
            $days = $duration * 30;
            break;
    }
    $setOfThreeDays = bcdiv($days / 3, 1, 0);
    $infectionsByRequestedTime = $currentlyInfected * pow(2, $setOfThreeDays);
    $dollarsInFlight = bcdiv(($infectionsByRequestedTime * $avgDailyIncomeInUSD * $avgDailyIncomePopulation) / $days, 1, 0);

    return ['infectionsByRequestedTime' => $infectionsByRequestedTime, 'dollarsInFlight' => $dollarsInFlight];
}
