<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     Amazon_Mturk
 *  @version     2008-08-02
 *  @author      Sandip Limbachiya(sandip2451984@gmail.com)
 */

/*************************************************************************
   mTurk PHP API V0.5b
   -----------------------------------------------------------------------
   Required PHP Libraries:
     mhash
     cURL

*************************************************************************/

class MTurkInterface
{
   /* Constructor Vars */
   var $Service;
   var $baseURL;
   var $soapURL;
   var $SecretKey;
   var $AccessKey;
   var $Version;
   var $ResponseGroup;
   var $Validate;
   var $Credential;

   /* Required Vars */
   var $Signature;
   var $Operation;
   var $Fault;
   var $Error;
   var $Result;

   /* Return Vars */
   var $ArrayData;
   var $FinalData;
   var $QueryData;
   var $QueryString;
   var $SOAPData;
   var $RawData;
   var $SOAPSwitch;

   /* Processed Vars */
   var $AssignmentList;
   var $HITList;
   var $IsValid;
   var $QualificationRequestList;
   var $RequestId;
   var $ResultCounter;
   var $ResultData;
   var $ResultPosition;
   var $ResultsTotal;
   var $AvailableBalance;

   /** Sanity Checks **/
   /* Valid operations, includes types added 10-01-2006 after RegisterHITType */
   var $validOps   = array("ApproveAssignment", "CreateHIT", "CreateQualificationType", "DisableHIT", "DisposeHIT", "ExtendHIT",
      "GetAccountBalance", "GetAssignmentsForHIT", "GetHIT", "GetQualificationRequests", "GetQualificationScore",
      "GetQualificationType", "GetRequesterStatistic", "GetReviewableHITs", "GrantQualification", "Help", "NotifyWorkers",
      "RejectAssignment", "SearchQualificationTypes", "UpdateQualificationScore", "UpdateQualificationType",
      "SetHITAsReviewing", "RegisterHITType", "SearchHITs", "ForceExpireHIT", "SetHITTypeNotification", "SendTestEventNotification",
      "GrantBonus", "BlockWorker", "UnblockWorker", "GetFileUploadURL", "RejectQualificationRequest", "GetQualificationsForQualificationType");
   /* Valid statistics, not fully in use yet */
   var $validStats = array("NumberAssignmentsAvailable", "NumberAssignmentsAccepted", "NumberAssignmentsPending",
      "NumberAssignmentsApproved", "NumberAssignmentsRejected", "NumberAssignmentsReturned", "NumberAssignmentsAbandoned",
      "PercentAssignmentsApproved", "PercentAssignmentsRejected", "TotalRewardPayout", "AverageRewardAmount",
      "TotalFeePayout", "TotalRewardAndFeePayout", "NumberHITsCreated", "NumberHITsCompleted", "NumberHITsAssignable",
      "NumberHITsReviewable", "EstimatedRewardLiability", "EstimatedFeeLiability", "EstimatedTotalLiability");
   var $validQTS   = array("Active", "Inactive"); /* Qualification Type Status */
   var $validSP    = array("AcceptTime", "SubmitTime", "AssignmentStatus");  /* Sort Property for GetAssignmentsForHIT */
   var $validGRHSP = array("Title", "Reward", "Expiration", "CreationTime"); /* Sort Property for GetReviewableHITs */
   var $validGQRSP = array("QualificationTypeId", "SubmitTime");             /* Sort Property for GetQualificationRequests */
   var $validSD    = array("Ascending", "Descending"); /* Sort Direction */
   var $validTP    = array("OneDay", "SevenDays", "ThirtyDays", "LifeToDate");
   var $validHT    = array("Operation", "ResponseGroup", "AssignmentSummary");
   var $validMBR   = array("true", "false"); /* Simple Boolean */
   var $validCPT   = array("LessThan", "LessThanOrEqualTo", "GreaterThan", "GreaterThanOrEqualTo", "EqualTo",
      "NotEqualTo", "Exists");
   var $validSMO   = array("Reviewable", "Reviewing"); /* StatusMatchOption, or now just Status */
   var $validAS    = array("Submitted", "Approved", "Rejected"); /* Assignment Status */
   var $validET    = array("AssignmentAccepted", "AssignmentAbandoned", "AssignmentReturned", "AssignmentSubmitted", "HITReviewable", "HITExpired", "Ping"); /* Event Type */
   var $validGQS   = array("Granted", "Revoked"); /* Status for GetQualificationsForQualificationType */
   var $validCC	   = array("USD"); /* check currency of amount and rewards */

   /* Used by Various Calls */
   var $About;
   var $Amount;
   var $AssignmentDurationInSeconds;
   var $AssignmentId;
   var $AutoApprovalDelayInSeconds;
   var $BonusAmount;
   var $Comparator;
   var $Count;
   var $CurrencyCode;
   var $Description;
   var $ExpirationIncrementInSeconds;
   var $HelpType;
   var $HITId;
   var $HITTypeId;
   var $IntegerValue;
   var $Keywords;
   var $LifetimeInSeconds;
   var $MaxAssignments;
   var $MaxAssignmentsIncrement;
   var $MessageText;
   var $MustBeRequestable;
   var $Name;
   var $Notification;
   var $PageSize;
   var $PageNumber;
   var $Price;
   var $QualificationRequestId;
   var $QualificationRequirement;
   var $QualificationTypeId;
   var $QualificationTypeStatus;
   var $Query;
   var $Question;
   var $QuestionIdentifier;
   var $Reason;
   var $RequesterAnnotation;
   var $RetryDelayInSeconds;
   var $Revert;
   var $SortProperty;
   var $SortDirection;
   var $Statistic;
   var $Status;
   var $Subject;
   var $SubjectId;
   var $TestDurationInSeconds;
   var $TestEventType;
   var $TimePeriod;
   var $Title;
   var $Value;
   var $WorkerId;

   /* XML Data Structure Vars */
   // var $Question;
   var $Test;
   var $AnswerKey;

   /* Constructor */
   function MTurkInterface($AccessKey, $SecretKey, $Version = "2008-08-02")
   {
      $this->Service       = "AWSMechanicalTurkRequester";
      // use this url for production mode
      // if(sfConfig::get('SF_MTURK_URL') == 'http://mechanicalturk.amazonaws.com/')
      // make it to production
      if (true)
      {
         $this->baseURL       = "http://mechanicalturk.amazonaws.com/onca/xml";
         $this->soapURL       = "http://mechanicalturk.amazonaws.com/onca/soap?Service={$this->Service}";
      }
      else
      {
         // This is for testing url
         $this->baseURL       = "http://mechanicalturk.sandbox.amazonaws.com/onca/xml";
         $this->soapURL       =  "http://mechanicalturk.sandbox.amazonaws.com?Service=AWSMechanicalTurkRequester";	
      }
      $this->SecretKey     = $SecretKey;
      $this->AccessKey     = $AccessKey;
      $this->Version       = $Version;
      // $this->ResponseGroup = "Minimal";

   }

   function SetOperation($operation)
   {  
      if   (!in_array($operation, $this->validOps)) return $this->mtError("Invalid Operation Type");
      else                                          $this->Operation = $operation;  //echo $this->Operation; exit;

      $this->About                        = 0;
      $this->Amount                       = 0;
      $this->AssignmentDurationInSeconds  = 0;
      $this->AssignmentId                 = 0;
      $this->AutoApprovalDelayInSeconds   = 0;
      $this->BonusAmount                  = 0;
      $this->Comparator                   = 0;
      $this->Count                        = 0;
      $this->CurrencyCode                 = 0;
      $this->Description                  = 0;
      $this->ExpirationIncrementInSeconds = 0;
      $this->HelpType                     = 0;
      $this->HITId                        = 0;
      $this->HITTypeId                    = 0;
      $this->IntegerValue                 = 0;
      $this->Keywords                     = 0;
      $this->LifetimeInSeconds            = 0;
      $this->MaxAssignments               = 0;
      $this->MaxAssignmentsIncrement      = 0;
      $this->MessageText                  = 0;
      $this->MustBeRequestable            = 0;
      $this->Name                         = 0;
      $this->Notification                 = 0;
      $this->PageSize                     = 0;
      $this->PageNumber                   = 0;
      $this->Price                        = 0;
      $this->QualificationRequestId       = 0;
      $this->QualificationRequirement     = 0;
      $this->QualificationTypeId          = 0;
      $this->QualificationTypeStatus      = 0;
      $this->Query                        = 0;
      $this->Question                     = 0;
      $this->QuestionIdentifier           = 0;
      $this->Reason                       = 0;
      $this->RequesterAnnotation          = 0;
      $this->RetryDelayInSeconds          = 0;
      $this->Revert                       = 0;
      $this->Reward                       = 0;
      $this->SortProperty                 = 0;
      $this->SortDirection                = 0;
      $this->Statistic                    = 0;
      $this->Status                       = 0;
      $this->Subject                      = 0;
      $this->SubjectId                    = 0;
      $this->TestDurationInSeconds        = 0;
      $this->TestEventType                = 0;
      $this->TimePeriod                   = 0;
      $this->Title                        = 0;
      $this->Value                        = 0;
      $this->WorkerId                     = 0;

      $this->ResultCounter                = 0;
      $this->ResultsTotal                 = 0;

      $this->Question                     = 0;
      $this->Test                         = 0;
      $this->AnswerKey                    = 0;

      return TRUE;
   }

   /* Sets a class variable - slower but guarantees it exists */
   function SetVar($var, $val)
   {
      if   (isset($this->$var)) $this->$var = $val;
      else                      return $this->mtError("Invalid Variable");
      return TRUE;
   }

   /* Primary Invokation Function */
   function Invoke()
   {   
      if     (!$this->Operation)                                  return $this->mtError("Missing Operation Parameter");
      elseif (!in_array($this->Operation, $this->validOps))       return $this->mtError("Invalid Operation Type");
      elseif (!$this->SecretKey || strlen($this->SecretKey) < 32) return $this->mtError("Missing/Invalid Secret Key");
      elseif (!$this->AccessKey || strlen($this->AccessKey) < 16) return $this->mtError("Missing/Invalid Access Key");
      if     (!$this->Timestamp)                                  $this->Timestamp = $this->Unix2ISO8601(time());

      $this->QueryData                   = array(); /* RESET ALL DATA HACK */
      $this->Fault                       = "";      /* RESET */
      $this->Error                       = "";      /* RESET */

      $this->Signature                   = $this->mtHMAC($this->Service, $this->Operation, $this->Timestamp);
      $this->QueryData['Service']        = $this->Service;
      $this->QueryData['Version']        = $this->Version;
      $this->QueryData['Operation']      = $this->Operation;
      $this->QueryData['AWSAccessKeyId'] = $this->AccessKey;
      $this->QueryData['Timestamp']      = $this->Timestamp;
      $this->QueryData['Signature']      = $this->Signature;

      if ($this->ReponseGroup)     $this->QueryData['ResponseGroup']  = $this->ResponseGroup;
      if ($this->Validate == TRUE) $this->QueryData['Validate'] = "true";

      if (!method_exists($this, $this->Operation)) return $this->mtError("Operation Not Yet Supported :(");


      $this->RawData   = eval("return \$this->{$this->Operation}();"); 

      if(is_string($this->RawData))
      { 
         $resultName      = "{$this->Operation}Response";

         /* Location Finding */
         switch ($this->Operation)
         {
         case "CreateHIT": 
            $responseName = "HIT"; 
            break;

         case "GetHIT":
            $responseName = "HIT";
            break;

         case "GetAssignmentsForHIT":
            $responseName = "{$this->Operation}Result";
            $cutName      = "Assignment";
            break;

            case "GetQualificationRequests";
            $responseName = "{$this->Operation}Result";
            $cutName      = "QualificationRequest";
            break;

         default:
            $responseName = "{$this->Operation}Result";
            break;
         } 


         $this->ArrayData = _xml2array($this->RawData);

         if ($this->SOAPSwitch)
         {
            $this->ArrayData = $this->ArrayData['SOAP-ENV:Envelope']['SOAP-ENV:Body'];
         }

         $this->RequestId = $this->ArrayData[$resultName]['OperationRequest']['RequestId'];
         $this->IsValid   = $this->ArrayData[$resultName][$responseName]['Request']['IsValid'];

         if(isset($this->ArrayData[$resultName][$responseName]['Request']['Errors']))
         {
            $this->mtError("Errors Detected!");
         } 
         elseif (isset($cutName)) $this->FinalData = $this->ArrayData[$resultName][$responseName][$cutName];
         else                     $this->FinalData = $this->ArrayData[$resultName][$responseName];

         /* Result Counters */
         if   (isset($this->ArrayData[$resultName][$responseName]['NumResults']))
         {
            $this->ResultCounter = $this->ArrayData[$resultName][$responseName]['NumResults'];
         }

         if   (isset($this->ArrayData[$resultName][$responseName]['TotalNumResults']))
         {
            $this->ResultsTotal = $this->ArrayData[$resultName][$responseName]['TotalNumResults'];
         }

         /* Special Data Handling */
         switch ($this->Operation)
         {
         case "CreateHIT": 
            $this->HITList = array($this->FinalData['HITId']);  
            break;

         case "GetReviewableHITs":
            $hits = array();
            if     ($this->ResultCounter == 1) $hits[] = $this->FinalData['HIT']['HITId'];
            elseif ($this->ResultCounter > 1)  foreach ($this->FinalData['HIT'] as $hitkey => $hitgroup) $hits[] = $hitgroup['HITId'][$hitkey];
            $this->HITList = $hits;
            break;

         case "GetAssignmentsForHIT":
            $assignments = array();
            if     ($this->ResultCounter == 1) $assignments[] = $this->FinalData;
            elseif ($this->ResultCounter > 1)  $assignments   = $this->FinalData;
            $this->AssignmentList = $assignments;
            break;

         case "GetQualificationRequests":
            $qualrequests = array();
            if     ($this->ResultCounter == 1) $qualrequests[] = $this->FinalData;
            elseif ($this->ResultCounter > 1)  $qualrequests   = $this->FinalData;
            $this->QualificationRequestList = $qualrequests;
            break;
         }
      }

      if (!$this->Fault && !$this->Error)
      {
         return TRUE;
      }
      else
      {
         return FALSE;
      }
   }

   /* Approve a HIT Assignment */
   function ApproveAssignment()
   {
      if     (!$this->AssignmentId)                                                return $this->mtError("Missing AssignmentId");
      else                                                                         $this->QueryData['AssignmentId'] = $this->AssignmentId;
      if     ($this->RequesterFeedback && strlen($this->RequesterFeedback) > 1024) return $this->mtError("RequesterFeedback entry is too long!");
      elseif ($this->RequesterFeedback)                                            $this->QueryData['RequesterFeedback'] = $this->RequesterFeedback;
      return $this->mtMakeRequest();
   }

   /* Create a new HIT - SOAP Operation understands HITTypeId */
   function CreateHIT()
   {  
      if     ($this->HITTypeId && $this->mtBreaksHTI()) return $this->mtError("Incompatible mixing of HITTypeId and other values");
      elseif (!$this->HITTypeId)
      {
         /* Values only applicable without HITTypeID */
         if     (!$this->Title)                            return $this->mtError("Missing Title Parameter");
         elseif (!$this->Description)                      return $this->mtError("Missing Description Parameter");
         elseif (!$this->Amount)                           return $this->mtError("Missing Amount Parameter");
         if     (!$this->CurrencyCode)                     $this->CurrencyCode = "USD";
         elseif (!$this->AssignmentDurationInSeconds)      return $this->mtError("Missing AssignmentDurationInSeconds Parameter");

         /* Qualification Array Checking */

         if(is_array($this->QualificationRequirement))
         {  
            foreach ($this->QualificationRequirement as $itr => $val)
            { 
               $fg = "(Group {$itr})";
               if     (!$val['QualificationTypeId'])                   return $this->mtError("Missing QualificationTypeId Parameter {$fg}");
               elseif (!$val['Comparator'])                            return $this->mtError("Missing Comparator Parameter {$fg}");
               elseif (!in_array($val['Comparator'], $this->validCPT)) return $this->mtError("Invalid Comparator Parameter {$fg}");
               if ($val['QualificationTypeId'] == "00000000000000000071") {
                  /* Locale Value */
                  if (!$val['LocaleValue']) return $this->mtError("Need a LocaleValue to accompany a Locale Qualification {$fg}");
               } else {
                  /* Regular Value */
                  if (!isset($val['IntegerValue']) || !is_numeric($val['IntegerValue'])) return $this->mtError("Need an InterValue to accompany a qualification {$fg}");
               }
               if (isset($val['RequiredToPreview']) && !in_array($val['RequiredToPreview'], $this->validMBR)) return $this->mtError("Invalid RequiredToPreview value (true/false) {$fg}");
            }
         }
      }

      /* Required values for either type */
      if     (!$this->LifetimeInSeconds)                return $this->mtError("Missing LifetimeInSeconds Parameter");
      elseif (!$this->Question)                         return $this->mtError("Missing Question Parameter");


      return $this->mtFakeSoap();
   }

   /* Create a new qualification */
   function CreateQualificationType()
   {
      if     (!$this->Name)                                               return $this->mtError("Missing Name Parameter");
      elseif (!$this->Description)                                        return $this->mtError("Missing Description Parameter");
      elseif (!$this->QualificationTypeStatus)                            return $this->mtError("Missing QualificationTypeStatus Parameter");
      elseif (!in_array($this->QualificationTypeStatus, $this->validQTS)) return $this->mtError("Invalid QualificationTypeStatus Value");
      elseif ($this->AnswerKey && !$this->Test)                           return $this->mtError("AnswerKey cannot be provided without Test!");
      return $this->mtFakeSoap();
   }

   /* Disable a HIT */
   function DisableHIT()
   {
      if     (!$this->HITId)        return $this->mtError("Missing HITId Parameter");
      else                          $this->QueryData['HITId'] = $this->HITId;
      return $this->mtMakeRequest();
   }

   /* Dispose of a HIT */
   function DisposeHIT()
   {
      if     (!$this->HITId)        return $this->mtError("Missing HITId Parameter");
      else                          $this->QueryData['HITId'] = $this->HITId;
      return $this->mtMakeRequest();
   }

   /* Extend the timing on a HIT */
   function ExtendHIT()
   {
      if     (!$this->HITId)                       return $this->mtError("Missing HITId Parameter");
      else                                         $this->QueryData['HITId']                        = $this->HITId;
      if     ($this->MaxAssignmentsIncrement)      $this->QueryData['MaxAssignmentsIncrement']      = $this->MaxAssignmentsIncrement;
      if     ($this->ExpirationIncrementInSeconds) $this->QueryData['ExpirationIncrementInSeconds'] = $this->ExpirationIncrementInSeconds;
      return $this->mtMakeRequest();
   }

   /* Force a HIT to expire immediately, as if the HIT's LifetimeInSeconds had elapsed */
   function ForceExpireHIT() {
      if     (!$this->HITId)                       return $this->mtError("Missing HITId Parameter");
      else                                         $this->QueryData['HITId']                        = $this->HITId;
      return $this->mtMakeRequest();
   }

   /* Fetch Account Balance */
   function GetAccountBalance()
   {
      return $this->mtMakeRequest();
   }

   /* Get a list of assignments for a given HIT Id */
   function GetAssignmentsForHIT()
   {
      if     (!$this->HITId)                                                                  return $this->mtError("Missing HITId Parameter");
      else                                                                                    $this->QueryData['HITId']         = $this->HITId;
      if     ($this->AssignmentStatus && !in_array($this->AssignmentStatus, $this->$validAS)) return $this->mtError("Invalid Assignment Status Value (Submitted/Approved/Rejected)");
      elseif ($this->AssignmentStatus)                                                        $this->QueryData['AssignmentStatus'] = $this->AssignmentStatus;
      if     ($this->SortProperty && !in_array($this->SortProperty, $this->$validSP))         return $this->mtError("Invalid Sort Property Value (AcceptTime/SubmitTime/AssignmentStatus");
      elseif ($this->SortProperty)                                                            $this->QueryData['SortProperty']  = $this->SortProperty;
      if     ($this->SortDirection && !in_array($this->SortDirection, $this->validSD))        return $this->mtError("Invalid SortDirection Value (Ascending/Descending)");
      elseif ($this->SortDirection)                                                           $this->QueryData['SortDirection'] = $this->SortDirection;
      if     (is_numeric($this->PageSize) && $this->PageSize > 0)                             $this->QueryData['PageSize']      = $this->PageSize;
      if     (is_numeric($this->PageNumber) && $this->PageNumber > 0)                         $this->QueryData['PageNumber']    = $this->PageNumber;

      return $this->mtMakeRequest();
   }

   function GetFileUploadURL()
   {  
      if   (!$this->AssignmentId)                    return $this->mtError("Missing AssignmentId");
      else                                           $this->QueryData['AssignmentId']       = $this->AssignmentId;
      if   (!$this->QuestionIdentifier)              return $this->mtError("Missing QuestionIdentifier");
      else                                           $this->QueryData['QuestionIdentifier'] = $this->QuestionIdentifier;  
      return $this->mtMakeRequest();
   }

   /* Get a Single HIT */
   function GetHIT()
   {
      if     (!$this->HITId) return $this->mtError("Missing HITId Parameter");
      else                   $this->QueryData['HITId'] = $this->HITId;
      return $this->mtMakeRequest();
   }

   /* Get qualification requests list */
   function GetQualificationRequests()
   {
      if     (!$this->QualificationTypeId)                                               return $this->mtError("Missing QualificationTypeId Parameter");
      else                                                                               $this->QueryData['QualificationTypeId']     = $this->QualificationTypeId;
      if     (is_numeric($this->PageSize) && $this->PageSize > 0)                        $this->QueryData['PageSize']      = $this->PageSize;
      if     (is_numeric($this->PageNumber) && $this->PageNumber > 0)                    $this->QueryData['PageNumber']    = $this->PageNumber;
      if     ($this->SortProperty && !in_array($this->SortProperty, $this->$validGQRSP)) return $this->mtError("Invalid Sort Property Value (AcceptTime/SubmitTime/AssignmentStatus");
      elseif ($this->SortProperty)                                                       $this->QueryData['SortProperty']  = $this->SortProperty;
      if     ($this->SortDirection && !in_array($this->SortDirection, $this->validSD))   return $this->mtError("Invalid SortDirection Value (Ascending/Descending)");
      elseif ($this->SortDirection)                                                      $this->QueryData['SortDirection'] = $this->SortDirection;
      return $this->mtMakeRequest();
   }

   /* Return a User's Qualification Score */
   function GetQualificationScore()
   {
      if     (!$this->QualificationTypeId) return $this->mtError("Missing QualificationTypeId Parameter");
      else                                 $this->QueryData['QualificationTypeId'] = $this->QualificationTypeId;
      if     (!$this->SubjectId)           return $this->mtError("Missing SubjectId Parameter");
      else                                 $this->QueryData['SubjectId']           = $this->SubjectId;
      return $this->mtMakeRequest();
   }

   /* Returns all of the Qualifications granted to Workers for a given Qualification type */
   function GetQualificationsForQualificationType()
   {
      if     (!$this->QualificationTypeId)                                return $this->mtError("Missing QualificationTypeId Parameter");
      else                                                                $this->QueryData['QualificationTypeId'] = $this->QualificationTypeId;
      if     ($this->Status && !in_array($this->Status, $this->validGQS)) return $this->mtError("Invalid Status Parameter");
      else                                                                $this->QueryData['Status']              = $this->Status;
      if     (is_numeric($this->PageSize) && $this->PageSize > 0)         $this->QueryData['PageSize']      = $this->PageSize;
      if     (is_numeric($this->PageNumber) && $this->PageNumber > 0)     $this->QueryData['PageNumber']    = $this->PageNumber;
      return $this->mtMakeRequest();
   }

   /* Get qualification type data */
   function GetQualificationType()
   {
      if     (!$this->QualificationTypeId) return $this->mtError("Missing QualificationTypeId Parameter");
      else                                 $this->QueryData['QualificationTypeId'] = $this->QualificationTypeId;
      return $this->mtMakeRequest();
   }

   /* Retrieve various statistics */
   function GetRequesterStatistic()
   {
      if     (!$this->Statistic)                                                 return $this->mtError("Missing Statistic Parameter");
      elseif (!in_array($this->Statistic, $this->validStats))                    return $this->mtError("Invalid Statistic Type");
      else                                                                       $this->QueryData['Statistic'] = $this->Statistic;
      if     ($this->TimePeriod && !in_array($this->TimePeriod, $this->validTP)) return $this->mtError("Invalid TimePeriod Type");
      elseif ($this->TimePeriod)                                                 $this->QueryData['TimePeriod'] = $this->TimePeriod;
      if     (is_numeric($this->Count) && $this->Count > 0)                      $this->QueryData['Count']      = $this->Count;
      return $this->mtMakeRequest();
   }

   /* Get Reviewable HITs */
   function GetReviewableHITs()
   {
      if     ($this->HITTypeId)                                                         $this->QueryData['HITTypeId']     = $this->HITTypeId;
      if     ($this->Status && !in_array($this->Status, $this->validSMO))               return $this->mtError("Invalid Status Value (Reviewing/Reviewable - PREVIOUSLY StatusMatchOption)");
      elseif ($this->Status)                                                            $this->QueryData['Status']        = $this->Status;
      if     (is_numeric($this->PageSize) && $this->PageSize > 0)                       $this->QueryData['PageSize']      = $this->PageSize;
      if     (is_numeric($this->PageNumber) && $this->PageNumber > 0)                   $this->QueryData['PageNumber']    = $this->PageNumber;
      if     ($this->SortProperty && !in_array($this->SortProperty, $this->validGRHSP)) return $this->mtError("Invalid SortProperty Value (Title/Reward/Expiration/CreationTime)");
      elseif ($this->SortProperty)                                                      $this->QueryData['SortProperty']  = $this->SortProperty;
      if     ($this->SortDirection && !in_array($this->SortDirection, $this->validSD))  return $this->mtError("Invalid SortDirection Value (Ascending/Descending)");
      elseif ($this->SortDirection)                                                     $this->QueryData['SortDirection'] = $this->SortDirection;
      return $this->mtMakeRequest();
   }

   /* Issues a payment of money from your account to a Worker */
   function GrantBonus()
   {
      if     (!$this->WorkerId)                         return $this->mtError("Missing WorkerId Parameter(s)");
      elseif (!$this->AssignmentId)                     return $this->mtError("Missing AssignmentId");
      elseif (!$this->BonusAmount)                      return $this->mtError("Missing BonusAmount");
      if     (!$this->mtCheckPriceData(1, $this->BonusAmount)) return false;
      return $this->mtFakeSoap();
   }

   /* Issues a payment of money from your account to a Worker */
   function BlockWorker()
   {
      if     (!$this->WorkerId)                         return $this->mtError("Missing WorkerId Parameter(s)");
      elseif (!$this->Reason)                     	return $this->mtError("Missing Reason");

      return $this->mtFakeSoap();
   } 

   /* Unblock a Worker Request By: Prakash Panchal*/
   function UnblockWorker()
   {
      if     (!$this->WorkerId)        			return $this->mtError("Missing WorkerId Parameter");
      else                                      $this->QueryData['WorkerId'] = $this->WorkerId;
      if     ($this->Reason)                    $this->QueryData['Reason']   = $this->Reason;
      return $this->mtFakeSoap();
   }

   /* Grant a qualification score to a user */
   function GrantQualification()
   {
      if     (!$this->QualificationRequestId)        return $this->mtError("Missing QualificationRequestId Parameter");
      else                                           $this->QueryData['QualificationRequestId'] = $this->QualificationRequestId;
      if     (!is_numeric($this->IntegerValue))      return $this->mtError("Missing IntegerValue Parameter");
      else                                           $this->QueryData['IntegerValue']           = $this->Value;
      return $this->mtMakeRequest();
   }

   /* GET HELP! */
   function Help()
   {
      if     (!$this->HelpType)                           return $this->mtError("Missing HelpType Parameter");
      elseif (!in_array($this->HelpType, $this->validHT)) return $this->mtError("Invalid HelpType Type");
      else                                                $this->QueryData['HelpType'] = $this->HelpType;
      if     (!$this->About)                              return $this->mtError("Missing About Parameter");
      elseif (!in_array($this->About, $this->validOps))   return $this->mtError("Invalid About Type");
      else                                                $this->QueryData['About']    = $this->About;
      return $this->mtMakeRequest();
   }

   /* Send workers a message */
   function NotifyWorkers()
   {
      if     (!$this->Subject)           return $this->mtError("Missing Subject Parameter");
      else                               $this->QueryData['Subject']     = $this->Subject;
      if     (!$this->MessageText)       return $this->mtError("Missing MessageText Parameter");
      else                               $this->QueryData['MessageText'] = $this->MessageText;
      if     (!$this->WorkerId)          return $this->mtError("Missing WorkerId Parameter(s)");
      elseif (is_array($this->WorkerId)) foreach ($this->WorkerId as $wk => $wi) $this->QueryData[("WorkerId." . $wk + 1)] = $wi;
      else                               $this->QueryData['WorkerId'] = $this->WorkerId;
      return $this->mtMakeRequest();
   }

   /* Register a HIT Type, essentially the same as CreateHIT, minus the actual HIT creation */
   function RegisterHITType()
   {
      if     (!$this->Title)                       return $this->mtError("Missing Title Parameter");
      elseif (!$this->Description)                 return $this->mtError("Missing Description Parameter");
      elseif (!$this->Amount)                      return $this->mtError("Missing Amount Parameter");
      elseif (!$this->AssignmentDurationInSeconds) return $this->mtError("Missing AssignmentDurationInSeconds Parameter");
      elseif (!$this->LifetimeInSeconds)           return $this->mtError("Missing LifetimeInSeconds Parameter");
      if     (!$this->CurrencyCode)                $this->CurrencyCode = "USD";

      /* Qualification Array Checking */
      if     (is_array($this->QualificationRequirement))
      {
         foreach ($this->QualificationRequirement as $itr => $val)
         {
            $fg = "(Group {$itr})";
            if     (!$val['QualificationTypeId'])                   return $this->mtError("Missing QualificationTypeId Parameter {$fg}");
            elseif (!$val['Comparator'])                            return $this->mtError("Missing Comparator Parameter {$fg}");
            elseif (!in_array($val['Comparator'], $this->validCPT)) return $this->mtError("Invalid Comparator Parameter {$fg}");
            elseif (!$val['Value'])                                 return $this->mtError("Missing Value Parameter {$fg}");
            elseif (!is_numeric($val['Value']))                     return $this->mtError("Invalid Value Parameter {$fg}");
         }
      }

      return $this->mtFakeSoap();
   }

   /* Reject HIT Assigmnment */
   function RejectAssignment()
   {
      if   (!$this->AssignmentId)                                                  return $this->mtError("Missing AssignmentId");
      else                                                                         $this->QueryData['AssignmentId'] = $this->AssignmentId;
      if     ($this->RequesterFeedback && strlen($this->RequesterFeedback) > 1024) return $this->mtError("RequesterFeedback entry is too long!");
      elseif ($this->RequesterFeedback)                                            $this->QueryData['RequesterFeedback'] = $this->RequesterFeedback;
      return $this->mtMakeRequest();
   }

   /* Reject a Qualification Request */
   function RejectQualificationRequest()
   {
      if     (!$this->QualificationRequestId)        return $this->mtError("Missing QualificationRequestId Parameter");
      else                                           $this->QueryData['QualificationRequestId'] = $this->QualificationRequestId;
      if     ($this->Reason)                         $this->QueryData['Reason']                 = $this->Reason;
      return $this->mtMakeRequest();
   }

   /* Returns all HITs, except for HITs that have been disposed with the DisposeHIT  operation.*/
   function SearchHITs()
   {
      if     ($this->SortProperty && !in_array($this->SortProperty, $this->validGRHSP)) return $this->mtError("Invalid SortProperty Value (Title/Reward/Expiration/CreationTime)");
      elseif ($this->SortProperty)                                                      $this->QueryData['SortProperty']  = $this->SortProperty;
      if     ($this->SortDirection && !in_array($this->SortDirection, $this->validSD))  return $this->mtError("Invalid SortDirection Value (Ascending/Descending)");
      elseif ($this->SortDirection)                                                     $this->QueryData['SortDirection'] = $this->SortDirection;
      if     (is_numeric($this->PageSize) && $this->PageSize > 0)                       $this->QueryData['PageSize']      = $this->PageSize;
      if     (is_numeric($this->PageNumber) && $this->PageNumber > 0)                   $this->QueryData['PageNumber']    = $this->PageNumber;
      return $this->mtMakeRequest();
   }

   /* Search Qualification Types on keyword */
   function SearchQualificationTypes()
   {
      if     ($this->Query)                                                                     $this->QueryData['Query']      = $this->Query;
      if     (is_numeric($this->PageSize) && $this->PageSize > 0)                               $this->QueryData['PageSize']      = $this->PageSize;
      if     (is_numeric($this->PageNumber) && $this->PageNumber > 0)                           $this->QueryData['PageNumber']    = $this->PageNumber;
      if     ($this->SortProperty)                                                              $this->QueryData['SortProperty']  = $this->SortProperty;
      if     ($this->SortDirection && !in_array($this->SortDirection, $this->validSD))          return $this->mtError("Invalid SortDirection Value (Ascending/Descending)");
      elseif ($this->SortDirection)                                                             $this->QueryData['SortDirection'] = $this->SortDirection;
      if     ($this->MustBeRequestable && !in_array($this->MustBeRequestable, $this->validMBR)) return $this->mtError("Invalid MustBeRequestable Value (true/false)");
      elseif ($this->MustBeRequestable)                                                         $this->QueryData['MustBeRequestable'] = $this->MustBeRequestable;
      return $this->mtMakeRequest();
   }

   function SendTestEventNotification()
   {
      if     (is_array($this->Notification))
      {
         foreach ($this->Notification as $itr => $val)
         {
            if (!$this->mtCheckNotificationData($itr, $val)) return false;
            if (!isset($val['Version']) && $this->Version)   $this->Notification[$itr]['Version'] = $this->Version; /* Helpful */
         }
      }
      if     ($this->TestEventType && !in_array($this->TestEventType, $validET)) return $this->mtError("Invalid TestEventType Value");
      return $this->mtFakeSoap();
   }

   function SetHITAsReviewing()
   {
      if     (!$this->HITId) return $this->mtError("Missing HITId Parameter");
      else                   $this->QueryData['HITId'] = $this->HITId;
      if     ($this->Revert) $this->QueryData['Revert'] = 'true';
      else                   $this->QueryData['Revert'] = 'false';
      return $this->mtMakeRequest();
   }

   /* Creates, updates, disables or re-enables notifications for a HIT type */
   function SetHITTypeNotification()
   {
      if     (!$this->HITTypeId)                                          return $this->mtError("Missing HITTypeId Parameter");
      if     ($this->Active && !in_array($this->Active, $this->validMBR)) return $this->mtError("Invalid Active Parameter");
      /* Notification Array Checking */
      if     (is_array($this->Notification))
      {
         foreach ($this->Notification as $itr => $val)
         {
            if (!$this->mtCheckNotificationData($itr, $val)) return false;
            if (!isset($val['Version']) && $this->Version)   $this->Notification[$itr]['Version'] = $this->Version; /* Helpful */
         }
      }
      return $this->mtFakeSoap();
   }

   /* Update the qualification score for a user */
   function UpdateQualificationScore()
   {
      if     (!$this->QualificationTypeId)                          return $this->mtError("Missing QualificationTypeId Parameter");
      else                                                          $this->QueryData['QualificationTypeId'] = $this->QualificationTypeId;
      if     (!$this->SubjectId)                                    return $this->mtError("Missing SubjectId Parameter");
      else                                                          $this->QueryData['SubjectId']           = $this->SubjectId;
      if     (!is_numeric($this->IntegerValue))                     return $this->mtError("Invalid IntegerValue Parameter");
      elseif ($this->IntegerValue < 0 || $this->IntegerValue > 100) return $this->mtError("Invalid IntegerValue Parameter");
      else                                                          $this->QueryData['IntegerValue'] = $this->IntegerValue;
      return $this->mtMakeRequest();
   }

   /* Update Qualification - RTB 03/10/06 - Switched to SOAP after API update */
   function UpdateQualificationType()
   {
      if     (!$this->QualificationTypeId)                                                                   return $this->mtError("Missing QualificationTypeId Parameter");
      if     ($this->QualificationTypeStatus && !in_array($this->QualificationTypeStatus, $this->$validQTS)) return $this->mtError("Invalid QualificationTypeStatus Value (Active/Inactive)");
      return $this->mtFakeSoap();
   }

   /* Makes a request to the AWS API */
   function mtMakeRequest()
   {
      $this->SOAPSwitch = FALSE; /* We ARE NOT making a SOAP request */

      foreach ($this->QueryData as $a => $b) $callData[] = "{$a}=" . urlencode($b);
      $this->QueryData   = $this->baseURL . "?" . implode($callData, "&");
      $this->QueryString = $this->QueryData;

      $handler = @fopen($this->QueryData, "r");

      if   (!$handler) return $this->mtError("Failed to contact host!");
      else
      { 
         while (!feof($handler)) $data .= fgets($handler, 2048); 
         fclose($handler);

         if   (strlen($data) < 4)
         {
            $this->mtError("No data returned");
            $return = FALSE;
         }
         else
         { 
            $return = $data;
         }
      }

      return $return;
   }

   /* Makes a fake SOAP request (For CreateHIT & CreateQualificationType) */
   function mtFakeSoap()
   {
      $this->SOAPSwitch = TRUE; /* We ARE making a SOAP request */

      /* Qualification List */
      if (is_array($this->QualificationRequirement))
      {
         $quals = "";

         foreach ($this->QualificationRequirement as $foo)
         {  
            $quals .= "<m:QualificationRequirement>\n";
            foreach ($foo as $key => $val) $quals .= "<m:{$key}>{$val}</m:{$key}>\n";
            $quals .= "</m:QualificationRequirement>\n";
            //echo $quals; exit;
         }
      }

      /* Notification Lists */
      if (is_array($this->Notification))
      {
         $notifications = "";
         foreach ($this->Notification as $foo)
         {
            $notifications .= "<m:Notification>\n";
            foreach ($foo as $key => $val)
            {
               if (!is_array($val)) $notifications .= "<m:{$key}>{$val}</m:{$key}>\n";
               else
               {
                  /* Covering multiple event types */
                  foreach ($val as $rar)
                  {
                     $notifications .= "<m:{$key}>{$rar}</m:{$key}>\n";
                  }
               }
            }
            $notifications .= "</m:Notification>\n";
         }
      }
      // echo $this->Operation; exit;
      $data  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      $data .= "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" ";
      $data .= "xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" ";
      $data .= "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" ";
      $data .= "xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">\n";
      $data .= "<SOAP-ENV:Body>\n";
      // if(sfConfig::get('SF_MTURK_URL') == 'http://mechanicalturk.amazonaws.com/')
      // make it to production
      if (true)
      {
         // for production environment
         $data .= "<m:{$this->Operation} xmlns:m=\"http://mechanicalturk.amazonaws.com/AWSMechanicalTurkRequester/{$this->Version}\">\n";
      }
      else
      {
         // for testing environment 	
         $data .= "<m:{$this->Operation} xmlns:m=\"http://mechanicalturk.sandbox.amazonaws.com/AWSMechanicalTurkRequester/{$this->Version}\">\n";
      }
      $data .= "<m:AWSAccessKeyId>{$this->AccessKey}</m:AWSAccessKeyId>\n";
      $data .= "<m:Signature>{$this->Signature}</m:Signature>\n";
      $data .= "<m:Timestamp>{$this->Timestamp}</m:Timestamp>\n";
      $data .= ($this->Validate ? "<m:Validate>true</m:Validate>\n" : "");
      $data .= ($this->ResponseGroup ? "<m:ResponseGroup>{$this->ResponseGroup}</m:ResponseGroup>\n" : "");
      $data .= "<m:Request>\n";
      switch ($this->Operation)
      {
      case "CreateHIT":
         /* HITTypeId Support Check */
         if ($this->HITTypeId)
         {
            $data .= "<m:HITTypeId>{$this->HITTypeId}</m:HITTypeId>\n";
         }
         else
         {
            $data .= "<m:Title>{$this->Title}</m:Title>\n";
            $data .= "<m:Description>{$this->Description}</m:Description>\n";
            $data .= ($this->Keywords ? "<m:Keywords>{$this->Keywords}</m:Keywords>\n" : "");
            $data .= "<m:AssignmentDurationInSeconds>{$this->AssignmentDurationInSeconds}</m:AssignmentDurationInSeconds>\n";
            $data .= ($this->AutoApprovalDelayInSeconds ? "<m:AutoApprovalDelayInSeconds>{$this->AutoApprovalDelayInSeconds}</m:AutoApprovalDelayInSeconds>\n" : "");
            $data .= "<m:Reward>\n";
            $data .= "<m:Amount>{$this->Amount}</m:Amount>\n";
            $data .= "<m:CurrencyCode>{$this->CurrencyCode}</m:CurrencyCode>\n";
            $data .= "</m:Reward>\n";
            $data .= (isset($quals) ? $quals : "");
         }

         $data .= ($this->MaxAssignments ? "<m:MaxAssignments>{$this->MaxAssignments}</m:MaxAssignments>\n" : "");
         $data .= ($this->RequesterAnnotation ? "<m:RequesterAnnotation>{$this->RequesterAnnotation}</m:RequesterAnnotation>\n" : "");
         $data .= "<m:LifetimeInSeconds>{$this->LifetimeInSeconds}</m:LifetimeInSeconds>\n";
         $data .= "<m:Question>" . htmlentities($this->Question) . "</m:Question>\n";
         break;

      case "RegisterHITType":
         $data .= "<m:Title>{$this->Title}</m:Title>\n";
         $data .= "<m:Description>{$this->Description}</m:Description>\n";
         $data .= ($this->Keywords ? "<m:Keywords>{$this->Keywords}</m:Keywords>\n" : "");
         $data .= ($This->AutoApprovalDelayInSeconds ? "<m:AutoApprovalDelayInSeconds>{$this->AutoApprovalDelayInSeconds}</m:AutoApprovalDelayInSeconds>\n" : "");
         $data .= "<m:AssignmentDurationInSeconds>{$this->AssignmentDurationInSeconds}</m:AssignmentDurationInSeconds>\n";
         $data .= "<m:Reward>\n";
         $data .= "<m:Amount>{$this->Amount}</m:Amount>\n";
         $data .= "<m:CurrencyCode>{$this->CurrencyCode}</m:CurrencyCode>\n";
         $data .= "</m:Reward>\n";
         $data .= (isset($quals) ? $quals : "");
         break;

      case "CreateQualificationType":
         $data .= "<m:Name>{$this->Name}</m:Name>\n";
         $data .= "<m:Description>{$this->Description}</m:Description>\n";
         $data .= ($this->Keywords ? "<m:Keywords>{$this->Keywords}</m:Keywords>\n" : "");
         $data .= ($this->RetryDelayInSeconds ? "<m:RetryDelayInSeconds>{$this->RetryDelayInSeconds}</m:RetryDelayInSeconds>\n" : "");
         $data .= "<m:QualificationTypeStatus>{$this->QualificationTypeStatus}</m:QualificationTypeStatus>\n";
         $data .= ($this->TestDurationInSeconds ? "<m:TestDurationInSeconds>{$this->TestDurationInSeconds}</m:TestDurationInSeconds>\n" : "");
         $data .= ($this->Test ? ("<m:Test>" . htmlentities($this->Test) . "</m:Test>") : "");
         $data .= ($this->AnswerKey ? ("<m:AnswerKey>" . htmlentities($this->AnswerKey) . "</m:AnswerKey>") : "");
         break;

      case "UpdateQualificationType":
         $data .= "<m:QualificationTypeId>{$this->QualificationTypeId}</m:QualificationTypeId>\n";
         $data .= ($this->Description ? "<m:Description>{$this->Description}</m:Description>\n" : "");
         $data .= ($this->QualificationTypeStatus ? "<m:QualificationTypeStatus>{$this->QualificationTypeStatus}</m:QualificationTypeStatus>\n" : "");
         $data .= ($this->Test ? ("<m:Test>" . htmlentities($this->Test) . "</m:Test>") : "");
         $data .= ($this->AnswerKey ? ("<m:AnswerKey>" . htmlentities($this->AnswerKey) . "</m:AnswerKey>") : "");
         $data .= ($this->TestDurationInSeconds ? "<m:TestDurationInSeconds>{$this->TestDurationInSeconds}</m:TestDurationInSeconds>\n" : "");
         break;

      case "SetHITTypeNotification":
         $data .= "<m:HITTypeId>{$this->HITTypeId}</m:HITTypeId>\n";
         $data .= (isset($notifications) ? $notifications : "");
         $data .= ($this->Active ? "<m:Active>{$this->Active}</m:Active>\n" : "");
         break;

      case "SendTestEventNotification":
         $data .= $notifications; /* This is required */
         $data .= ($this->TestEventType ? "<m:TestEventType>{$this->TestEventType}</m:TestEventType>\n" : "");
         break;

      case "GrantBonus":
         $data .= "<m:WorkerId>{$this->WorkerId}</m:WorkerId>\n";
         $data .= "<m:AssignmentId>{$this->AssignmentId}</m:AssignmentId>\n";
         $data .= "<m:Reward>\n";
         $data .= "<m:Amount>{$this->BonusAmount['Amount']}</m:Amount>\n";
         $data .= "<m:CurrencyCode>{$this->BonusAmount['CurrencyCode']}</m:CurrencyCode>\n";
         $data .= "</m:Reward>\n";
         $data .= ($this->Reason ? "<m:Reason>{$this->Reason}</m:Reason>\n" : "");
         break;

      case "BlockWorker":
         $data .= "<m:WorkerId>{$this->WorkerId}</m:WorkerId>\n";
         $data .= ($this->Reason ? "<m:Reason>{$this->Reason}</m:Reason>\n" : "");
         break;

      case "UnblockWorker":
         $data .= "<m:WorkerId>{$this->WorkerId}</m:WorkerId>\n";
         $data .= ($this->Reason ? "<m:Reason>{$this->Reason}</m:Reason>\n" : "");
         break;
      }
      $data .= "</m:Request>\n";
      $data .= "</m:{$this->Operation}>\n";
      $data .= "</SOAP-ENV:Body>\n";
      $data .= "</SOAP-ENV:Envelope>\n";

      $this->SOAPData = $data;

      $ch    = curl_init();
      curl_setopt($ch, CURLOPT_USERAGENT, 'Santa Cruz Tech MTurk Interface Script (SOAP Hack Version) V0.65');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0); /* 1 for return header output */
      curl_setopt($ch, CURLOPT_URL, $this->soapURL);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_Setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "SoapAction: \"\""));
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_Setopt($ch, CURLOPT_FAILONERROR, 1);
      ob_start();

      $return = curl_exec($ch);

      ob_end_clean();
      curl_close($ch);

      return $return;
   }

   /* Figures out if a HITTypeID is going to be busted because a field used for it is included */
   function mtBreaksHTI()
   {
      if ($this->Title || $this->Description || $this->Keywords || $this->Reward || $this->AssignmentDurationsInSeconds || $this->AutoApprovalDelayInSeconds || is_array($this->QualificationRequirement))
      {
         return TRUE;
      }
      return FALSE;
   }

   /* Figures out if an email is valid */
   function mtValidEmail($email)
   {
      return preg_match("/^[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+@((?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+|(\[([0-9]{1,3}(\.[0-9]{1,3}){3}|[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7})\]))$/", $email);
   }

   /* URL validation - SORT LATER */
   function mtValidHTTP($url)
   {
      return TRUE;
   }

   /* Checks Notification Data Structure */
   function mtCheckNotificationData($iteration, $data)
   {
      if     (!isset($data['Destination'])) return $this->mtError("Missing Destination Value (Notification Level {$iteration})");
      elseif (!isset($data['Transport']))   return $this->mtError("Missing Transport Value (Notification Level {$iteration} - Email/SOAP/REST)");
      elseif (!isset($data['EventType']))   return $this->mtError("Missing EventType Value (Notification Level {$iteration})");
      else
      {
         switch ($data['Transport'])
         {
         case "Email":
            if (!$this->mtValidEmail($data['Destination'])) return $this->mtError("Invalid email address as destination (Notification Level {$iteration})");
            break;
         case "SOAP":
         case "REST":
            if (!$this->mtValidHTTP($data['Destination'])) return $this->mtError("Invalid url as destination (Notification Level {$iteration})");
            break;
         }
      }

      if (is_array($data['EventType']))
      {
         foreach ($data['EventType'] as $checkMe)
         {
            if (!in_array($checkMe, $validET)) return $this->mtError("Invalid EventType Value '{$checkMe}' (Notification Level {$iteration})");
         }
      }
      elseif (!in_array($data['EventType'], $validET)) return $this->mtError("Invalid EventType Value (Notification Level {$iteration})");
      return true;
   }

   /* Checks Price Data Structure Info */
   function mtCheckPriceData($iteration, $data)
   { 
      if     (!isset($data['Amount']) || !is_numeric($data['Amount'])) return $this->mtError("Missing/Invalid Amount Specified (Price Level {$iteration})");
      elseif (!isset($data['CurrencyCode']))                           return $this->mtError("Missing Currency Code Specified (Price Level {$iteration})");
      elseif (!in_array($data['CurrencyCode'], $this->validCC))              return $this->mtError("Invalid Currency Code Specified (Price Level {$iteration})");
      return true;
   }

   /* Convert given date to ISO 8601 format */
   function Unix2ISO8601($int_date)
   {
      $int_date = $int_date + $this->mtHours(8);
      $date_mod = date('Y-m-d\TH:i:s', $int_date);
      $pre_timezone = date('O', $int_date);
      return $date_mod . ".000Z";
   }

   /* Convert given RFC 8601 Date to Unix Timestamp */
   function ISO86012Unix($timestamp)
   {
      $day    = substr($timestamp,8,2);
      $month  = substr($timestamp,5,2);
      $year   = substr($timestamp,0,4);
      $hour   = substr($timestamp,11,2);
      $minute = substr($timestamp,14,2);
      $second = substr($timestamp,17,2);
      $output = mktime($hour,$minute,$second,$month,$day,$year);
      return    $output;
   }

   function LoadQuestion($inputsource)
   {
      $data = $this->mtPullSource($inputsource);
      if   ($data) $this->Question = $data;
      else         return $data;
      return TRUE;
   }

   function LoadTest($inputsource)
   {
      $data = $this->mtPullSource($inputsource);
      if   ($data) $this->Test = $data;
      else         return $data;
      return TRUE;
   }

   function LoadAnswerKey($inputsource)
   {
      $data = $this->mtPullSource($inputsource); //print("<PRE>"); print_r($data); exit;
      if   ($data) $this->AnswerKey = $data;
      else         return $data;
      return TRUE;
   }

   /* Output hit list internal var */
   function PullHITList()
   {
      if   (is_array($this->HITList)) return $this->HITList;
      else                            return array();
   }

   function PullAssignmentList()
   {
      if   (is_array($this->AssignmentList)) return $this->AssignmentList;
      else                                   return array();
   }

   function PullQualificationRequestList()
   {
      if   (is_array($this->QualificationRequestList)) return $this->QualificationRequestList;
      else                                             return array();
   }

   function mtPullSource($inputsource)
   { 
      $fp    = @fopen($inputsource, "r"); 
      if (!$fp) return $this->mtError("Unable to access input source!");
      while (!feof($fp)) $input .= fgets($fp, 1024);
      fclose($fp);  
      return $input;
   }

   /* Simple Error Handler */
   function mtError($error)
   {
      $this->Fault = $error;
      return null;
   }

   /* Quick convert to minutes */
   function mtMinutes($input)
   {
      return $input * 60;
   }

   /* Quick convert to hours */
   function mtHours($input)
   {
      return $input * 60 * 60;
   }

   /* Quick convert to days */
   function mtDays($input)
   {
      return $input * 60 * 60 * 24;
   }

   /* Creates HMAC string */
   function mtHMAC($service, $operation, $timestamp)
   {
      return base64_encode(mhash(MHASH_SHA1, "{$service}{$operation}{$timestamp}", $this->SecretKey));
      //return base64_encode(get_loaded_extensions(MHASH_SHA1, "{$service}{$operation}{$timestamp}", $this->SecretKey));
   }

   function ParseAnswerList($answers)
   { //echo $answers; exit;
   //$xmlAnswer = simplexml_load_string($answers);	  var_dump($xmlAnswer); exit;
   /* BUGBUG: TO DO */
   }

   function utf8_to_unicode($str)
   {
      $unicode    = array();
      $values     = array();
      $lookingFor = 1;

      for ($i = 0; $i < strlen( $str ); $i++ )
      {
         $thisValue = ord( $str[ $i ] );

         if   ($thisValue < 128) $unicode[] = $thisValue;
         else
         {
            if (count($values) == 0) $lookingFor = ($thisValue < 224) ? 2 : 3;

            $values[] = $thisValue;

            if ( count( $values ) == $lookingFor )
            {
               $number     = ($lookingFor == 3 ) ? (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64) : (($values[0] % 32) * 64) + ($values[1] % 64);
               $unicode[]  = $number;
               $values     = array();
               $lookingFor = 1;
            }
         }
      }

      return $unicode;
   }

   function unicode_to_entities($unicode)
   {
      $entities = '';
      if (count($unicode) > 0) foreach ($unicode as $value) $entities .= '&#' . $value . ';';
      return $entities;
   }

   function fix_cr_entities($string)
   {
      return str_replace("&#13;", "\n", $string);
   }

   function convToUnicode($input)
   {  
      $gubbins = $this->utf8_to_unicode($input);
      $output  = $this->unicode_to_entities($gubbins);
      $output  = $this->fix_cr_entities($output);
      return $output;
   }

   function UnpackAnswers($key, $answerXML)
   { 
      $answerList = explode("\n", $key);

      $qualData   = _xml2array($answerXML);
      //echo "kkkkkkkkkkkkkkkk";   print("<PRE>"); print_r($answerList); exit;
      $answers    = array();

      foreach ($answerList as $ansData) {
         $ans               = explode(",", $ansData); 
         $ansName           = eval("return \$qualData{$ans[0]};"); // print("<PRE>"); print_r($ansName); exit;
         $answers[$ansName] = $this->convToUnicode(eval("return \$qualData{$ans[1]};"));
      }

      return $answers;
   }

   function mtLocale($country)
   {
      return "<m:Country>{$country}</m:Country>\n"; /*  */
   }
}



function parse_recursive(SimpleXMLElement $element, $level = 0)
{
   $indent     = str_repeat("\t", $level); // determine how much we'll indent

   $value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
   $attributes = $element->attributes();   // get all attributes
   $children   = $element->children();     // get all children

   echo "{$indent}Parsing '{$element->getName()}'...".PHP_EOL;
   if(count($children) == 0 && !empty($value)) // only show value if there is any and if there aren't any children
   {
      echo "{$indent}Value: {$element}".PHP_EOL;
   }

   // only show attributes if there are any
   if(count($attributes) > 0)
   {
      echo $indent.'Has '.count($attributes).' attribute(s):'.PHP_EOL;
      foreach($attributes as $attribute)
      {
         echo "{$indent}- {$attribute->getName()}: {$attribute}".PHP_EOL;
      }
   }

   // only show children if there are any
   if(count($children))
   {
      echo $indent.'Has '.count($children).' child(ren):'.PHP_EOL;
      foreach($children as $child)
      {
         parse_recursive($child, $level+1); // recursion :)
      }
   }

   echo $indent.PHP_EOL; // just to make it "cleaner"
}



/* XML to PHP Array Parse Function */
/* See http://beeblex.com/lists/index.php/php.notes/93773 */
function _xml2array($raw_xml)
{  

   $xml_parser = xml_parser_create(); 
   xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0); 
   xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
   //echo $raw_xml; exit;

   preg_match_all( "/\<Answer\>(.*?)<\/Answer\>/s", $raw_xml, $RowDataAry);

   xml_parse_into_struct($xml_parser, $raw_xml, $vals); 

   xml_parser_free($xml_parser);

   $xml_array = array();  
   $_tmp=''; 

   foreach ($vals as $xml_elem) {
      $x_tag=$xml_elem['tag'];
      $x_level=$xml_elem['level'];
      $x_type=$xml_elem['type'];


      if ($x_level!=1 && $x_type == 'close') {
         if (isset($multi_key[$x_tag][$x_level])) $multi_key[$x_tag][$x_level]=1;
         else                                     $multi_key[$x_tag][$x_level]=0;
      }
      if ($x_level!=1 && $x_type == 'complete') {
         if ($_tmp==$x_tag)                       $multi_key[$x_tag][$x_level]=1;
         $_tmp=$x_tag;


      }
   }

   foreach ($vals as $xml_elem) {
      // Start script by sandip
      if ($xml_elem['tag'] == 'Answer') { 
         $xml_elem['value'] = $RowDataAry[0][0]; 
      }
      // Ending of script

      $x_tag=$xml_elem['tag'];
      $x_level=$xml_elem['level'];
      $x_type=$xml_elem['type'];
      if ($x_type == 'open')               $level[$x_level] = $x_tag;
      $start_level = 1;
      $php_stmt = '$xml_array';
      if ($x_type=='close' && $x_level!=1) $multi_key[$x_tag][$x_level]++;
      while($start_level < $x_level) {
         $php_stmt .= '[$level['.$start_level.']]';
         if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level]) {
            $php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
         }
         $start_level++;
      } 
      $add='';
      if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type=='open' || $x_type=='complete')) {
         if   (!isset($multi_key2[$x_tag][$x_level])) $multi_key2[$x_tag][$x_level]=0;
         else                                         $multi_key2[$x_tag][$x_level]++;
         $add='['.$multi_key2[$x_tag][$x_level].']';
      }
      if (isset($xml_elem['value']) && trim($xml_elem['value'])!='' && !array_key_exists('attributes',$xml_elem)) {
         if   ($x_type == 'open') $php_stmt_main=$php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
         else                     $php_stmt_main=$php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';   
         eval($php_stmt_main);
      } 

      if (array_key_exists('attributes',$xml_elem)) {
         if (isset($xml_elem['value'])) {
            $php_stmt_main=$php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
            eval($php_stmt_main);

         }

         foreach ($xml_elem['attributes'] as $key=>$value) {
            $php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[\'attributes\'][$key] = $value;';
            eval($php_stmt_att);
         }
      }

   }   

   return $xml_array;
} 
?>
