package com.ss12.mapenlarge;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.Toast;

public class ResultActivity extends Activity {
	Button btn_good;
	Button btn_bad;
	
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.page_result);
        
        btn_good = (Button) findViewById(R.id.btn_good);
        btn_good.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				Toast.makeText(ResultActivity.this, "Good response!", Toast.LENGTH_LONG).show();
			}
		});
                
        btn_bad = (Button) findViewById(R.id.btn_bad);
        btn_bad.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				Toast.makeText(ResultActivity.this, "Bad response!", Toast.LENGTH_LONG).show();
			}
		});
    }
}