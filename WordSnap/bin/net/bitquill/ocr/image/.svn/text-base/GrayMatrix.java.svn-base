/**
 * Copyright 2009 Spiros Papadimitriou <spapadim@cs.cmu.edu>
 * 
 * This file is part of WordSnap OCR.
 * 
 * WordSnap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * WordSnap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WordSnap.  If not, see <http://www.gnu.org/licenses/>.
 */
package net.bitquill.ocr.image;

public class GrayMatrix {

    @SuppressWarnings("unused")
    private static final String TAG = "GrayMatrix";

    protected byte[] mData;
    protected int mWidth;
    protected int mHeight;

    // TODO - decide method scope
    protected GrayMatrix (int width, int height) {
        mWidth = width;
        mHeight = height;
        mData = new byte[width * height];
    }

    public GrayMatrix (byte[] data, int width, int height) {
        if (data.length < width * height) {
            throw new IllegalArgumentException("Image data array is too short");
        }
        mData = data;
        mWidth = width;
        mHeight = height;
    }

    /**
     * Copy constructor.
     * @param other  GrayMatrix to copy from.
     */
    public GrayMatrix (GrayMatrix other) {
        int width = other.mWidth;
        int height = other.mHeight;
        mWidth = width;
        mHeight = height;
        mData = new byte[width*height];
        System.arraycopy(other.mData, 0, mData, 0, width * height);
    }

    public final int getWidth () {
        return mWidth;
    }

    public final int getHeight () {
        return mHeight;
    }

    public final int get (int i, int j) {
        return GrayMatrix.getPixel(mData, mWidth, i, j);
    }

    public final void set (int i, int j, byte value) {
        GrayMatrix.setPixel(mData, mWidth, i, j, value);
    }

    public final void getRow (int i, byte[] values) {
        int width = mWidth;
        System.arraycopy(mData, i*width, values, 0, width);
    }

    // FIXME - remove this method??
    public final byte[] getData () {
        return mData;
    }
    
    protected static final byte getByte (byte[] data, int width, int i, int j) {
        return data[i*width + j];
    }

    protected static final int getPixel (byte[] data, int width, int i, int j) {
        return data[i*width + j] & 0xFF;
    }

    protected static final void setPixel (byte[] data, int width, int i, int j, byte value) {
        data[i*width + j] = value;
    }
}
