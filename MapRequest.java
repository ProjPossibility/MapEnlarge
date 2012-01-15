import java.util.Date;

import com.amazonaws.mturk.addon.HITDataCSVReader;
import com.amazonaws.mturk.addon.HITDataCSVWriter;
import com.amazonaws.mturk.addon.HITDataInput;
import com.amazonaws.mturk.addon.HITDataOutput;
import com.amazonaws.mturk.addon.HITProperties;
import com.amazonaws.mturk.addon.HITQuestion;
import com.amazonaws.mturk.requester.HIT;
import com.amazonaws.mturk.service.axis.RequesterService;
import com.amazonaws.mturk.util.PropertiesClientConfig;

public class MapRequest {

  // Defining the locations of the input files
  private RequesterService service;
  private String rootDir = ".";
  private String inputFile = rootDir + "/map_request.input";
  private String propertiesFile = rootDir + "/map_request.properties";
  private String questionFile = rootDir + "/map_request.question";

  public SiteCategory() {
    service = new RequesterService(new PropertiesClientConfig("../mturk.properties"));
  }

  /**
   * Check to see if there are sufficient funds.
   * @return true if there are sufficient funds.  False otherwise.
   */
	public boolean hasEnoughFund() {
    double balance = service.getAccountBalance();
    System.out.println("Got account balance: " + RequesterService.formatCurrency(balance));
    return balance > 0;
  }

  /**
   * Create the website category HITs.
   *
   */
  public void createSiteCategoryHITs() {
    try {

      //Loading the input file.  The input file is a tab delimited file where the first row
      //defines the fields/variables and the remaining rows contain the values for each HIT.
      //Each row represents a unique HIT.  The SDK uses the Apache Velocity engine to merge
      //the input values into a templatized QAP file.
      //Please refer to http://velocity.apache.org for more details on this engine and
      //how to specify variable names.  Apache Velocity is fully supported so there may be
      //additional functionality you can take advantage of if you know the Velocity syntax.
      HITDataInput input = new HITDataCSVReader(inputFile);

      //Loading the question (QAP) file.  This QAP file contains Apache Velocity variable names where the values
      //from the input file are to be inserted.  Essentially the QAP becomes a template for the input file.
      HITQuestion question = new HITQuestion(questionFile);

      //Loading the HIT properties file.  The properties file defines two system qualifications that will
      //be used for the HIT.  The properties file can also be a Velocity template.  Currently, only
      //the "annotation" field is allowed to be a Velocity template variable.  This allows the developer
      //to "tie in" the input value to the results.
      HITProperties props = new HITProperties(propertiesFile);

      HIT[] hits = null;

      // Create multiple HITs using the input, properties, and question files

      System.out.println("--[Loading HITs]----------");
      Date startTime = new Date();
      System.out.println("  Start time: " + startTime);

      //The simpliest way to bulk load a large number of HITs where all details are defined in files.
      //When using this method, it will automatically create output files of the following type:
      //  - <your input file name>.success - A file containing the HIT IDs and HIT Type IDs of
      //                                     all HITs that were successfully loaded.  This file will
      //                                     not exist if there are no HITs successfully loaded.
      //  - <your input file name>.failure - A file containing the input rows that failed to load.
      //                                     This file will not exist if there are no failures.
      //The .success file can be used in subsequent operations to retrieve the results that workers submitted.
      HITDataOutput success = new HITDataCSVWriter(inputFile + ".success");
      HITDataOutput failure = new HITDataCSVWriter(inputFile + ".failure");
      hits = service.createHITs(input, props, question, success, failure);

      System.out.println("--[End Loading HITs]----------");
      Date endTime = new Date();
      System.out.println("  End time: " + endTime);
      System.out.println("--[Done Loading HITs]----------");
      System.out.println("  Total load time: "
          + (endTime.getTime() - startTime.getTime())/1000 + " seconds.");

      if (hits == null) {
        throw new Exception("Could not create HITs");
      }

    } catch (Exception e) {
      System.err.println(e.getLocalizedMessage());
    }
  }

	/**
   * @param args
   */
	public static void main(String[] args) {

    MapRequest app = new MapRequest();

    if (app.hasEnoughFund()) {
      app.createSiteCategoryHITs();
    }
  }
}
