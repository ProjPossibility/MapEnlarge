package com.ss12.mapenlarge;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.HashMap;
import java.util.UUID;

import org.json.JSONException;

import android.app.Activity;
import android.content.ContentResolver;
import android.content.Intent;
import android.content.res.Resources.NotFoundException;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.Toast;

import com.google.android.maps.MapActivity;

public class ImageCaptureActivity extends MapActivity {
	private static final int TAKE_PICTURE = 0;
	
	Button btn_capture_image;
	Button btn_submit;
	ImageView iv_preview;
	
	Uri imageUri;
	
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.page_image_capture);
        
        // Init UI
        btn_capture_image = (Button) findViewById(R.id.btn_capture_image);
        btn_capture_image.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
			    Intent intent = new Intent("android.media.action.IMAGE_CAPTURE");
			    File photo = new File(Environment.getExternalStorageDirectory(),  "Pic.jpg");
			    intent.putExtra(MediaStore.EXTRA_OUTPUT,
			            Uri.fromFile(photo));
			    imageUri = Uri.fromFile(photo);
			    startActivityForResult(intent, TAKE_PICTURE);

			}
		});
        
        btn_submit = (Button) findViewById(R.id.btn_submit);
        btn_submit.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				// TODO SUBMIT
				// Get the byte array of the bitmap
				Bitmap bm = null;
				try {	                
	                HashMap<String, String> query = new HashMap<String, String>();
	                query.put(getResources().getString(R.string.web_service_uuid), UUID.randomUUID().toString());
	                HttpHelper.post(getResources().getString(R.string.web_service_url), query, getResources().getString(R.string.web_service_img), new File(imageUri.getPath()));
				} catch (FileNotFoundException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				} catch (NullPointerException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (NotFoundException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		});
        
        iv_preview = (ImageView) findViewById(R.id.iv_preview);
        
        /*
		Intent i = new Intent(ImageCaptureActivity.this, ResultReceiver.class);
		PendingIntent pendingIntent = PendingIntent.getBroadcast(getApplicationContext(), ResultReceiver.RECEIVER_INTENT, i, 0);
		AlarmManager alarmManager = (AlarmManager) getSystemService(Context.ALARM_SERVICE);
		alarmManager.set(AlarmManager.RTC_WAKEUP, System.currentTimeMillis() + 10000, pendingIntent);
		*/
    }

	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);

	    switch (requestCode) {
	    case TAKE_PICTURE:
	        if (resultCode == Activity.RESULT_OK) {
	            Uri selectedImage = imageUri;
	            getContentResolver().notifyChange(selectedImage, null);
	            ImageView imageView = (ImageView) findViewById(R.id.iv_preview);
	            ContentResolver cr = getContentResolver();
	            Bitmap bitmap;
	            try {
	                 bitmap = android.provider.MediaStore.Images.Media.getBitmap(cr, selectedImage);

	                imageView.setImageBitmap(bitmap);
	                Toast.makeText(this, selectedImage.toString(), Toast.LENGTH_LONG).show();
	            } catch (Exception e) {
	                Toast.makeText(this, "Failed to load", Toast.LENGTH_SHORT).show();
	                Log.e("Camera", e.toString());
	            }
	        }
	    }
	}

	@Override
	protected boolean isRouteDisplayed() {
		// TODO Auto-generated method stub
		return false;
	}
}